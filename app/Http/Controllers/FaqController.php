<?php

namespace App\Http\Controllers;

use App\Enums\FaqTypeEnum;
use Illuminate\Http\Request;
use App\Models\Faq;
use App\Models\FaqTranslation;
use App\Models\Language;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FaqController extends Controller
{
    public function __construct()
    {
        // Staff Permission Check
        $this->middleware(['permission:view_faqs'])->only('index');
        $this->middleware(['permission:add_faq'])->only('create', 'store');
        $this->middleware(['permission:edit_faq'])->only('edit', 'update');
        $this->middleware(['permission:delete_faq'])->only('destroy');
        $this->middleware(['permission:publish_faq'])->only('toggleStatus');
    }

    /**
     * Display a listing of the resource.
     *
     */
    public function index(Request $request)
    {
        $sort_search = null;
        $faq_type = null;
        $status = null;

        $faqs = Faq::with(['translations'])->ordered();

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $sort_search = $request->search;
            $faqs = $faqs->whereHas('translations', function($query) use ($sort_search) {
                $query->where('question', 'like', '%'.$sort_search.'%')
                      ->orWhere('answer', 'like', '%'.$sort_search.'%');
            });
        }

        // Type filter
        if ($request->has('faq_type') && !empty($request->faq_type)) {
            $faq_type = $request->faq_type;
            $faqs = $faqs->byType($faq_type);
        }

        // Status filter
        if ($request->has('status') && $request->status !== '') {
            $status = $request->status;
            if ($status == '1') {
                $faqs = $faqs->published();
            } elseif ($status == '0') {
                $faqs = $faqs->where('is_published', false);
            }
        }

        $faqs = $faqs->paginate(15);
        $types = FaqTypeEnum::keys();

        return view('backend.faq.faqs.index', compact('faqs', 'types', 'sort_search', 'faq_type', 'status'));
    }

    /**
     * Show the form for creating a new resource.
     *
     */
    public function create()
    {
        $categories =  $types = FaqTypeEnum::keys();
        return view('backend.faq.faqs.create', compact('types'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(Request $request)
    {
        try {
        $request->validate([
            'type' => 'required|in:' . implode(',', FaqTypeEnum::keys()),
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
            'sort_order' => 'nullable|integer|min:0',
            'slug' => 'nullable|string|max:255|unique:faqs,slug'
        ]);

        $faq = new Faq;
        $faq->type = $request->type;
        $faq->sort_order = $request->sort_order ?? 0;
        $faq->is_published = $request->has('is_published') ? true : false;
        $faq->view_count = 0;

        // Generate slug
        if ($request->slug != null) {
            $faq->slug = Str::slug($request->slug);
        } else {
            $faq->slug = Faq::generateUniqueSlug($request->question);
        }

        // Ensure slug uniqueness
        $originalSlug = $faq->slug;
        $counter = 1;
        while (Faq::where('slug', $faq->slug)->exists()) {
            $faq->slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $faq->save();

        // Save translation for default language
        $faq_translation = FaqTranslation::firstOrNew([
            'locale' => config('app.locale'),
            'faq_id' => $faq->id
        ]);
        $faq_translation->question = $request->question;
        $faq_translation->answer = $request->answer;
        $faq_translation->save();

        flash(translate('FAQ has been inserted successfully'))->success();
        return redirect()->route('faqs.index');

        } catch (\Exception $e) {
            flash(translate('An error occurred while saving the FAQ: ') . $e->getMessage())->error();
            return back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id

     */
    public function show($id)
    {
        $faq = Faq::with(['translations'])->findOrFail($id);
        return view('backend.faq.faqs.show', compact('faq'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id

     */
    public function edit(Request $request, Faq $faq)
    {
        $lang = $request->lang ?? config('app.locale');
        $faq->load(['translations']);
        $types = FaqTypeEnum::keys();

        return view('backend.faq.faqs.edit', compact('faq', 'types', 'lang'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     */
    public function update(Request $request, $id)
    {
        try {
        $faq = Faq::findOrFail($id);
        $lang = $request->lang ?? config('app.locale');

        $request->validate([
            'type' => 'required_if:lang,' . config('app.locale') . '|in:' . implode(',', FaqTypeEnum::keys()),
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
            'sort_order' => 'nullable|integer|min:0',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('faqs', 'slug')->ignore($faq->id)
            ]
        ]);

        // Only update main fields if editing default language
        if ($lang == config('app.locale')) {
            $faq->type = $request->type;
            $faq->sort_order = $request->sort_order ?? 0;
            $faq->is_published = $request->has('is_published') ? true : false;

            if ($request->slug != null) {
                $faq->slug = Str::slug($request->slug);
            } else {
                // Update slug based on question if no custom slug provided
                $faq->slug = Faq::generateUniqueSlug($request->question);
            }

            $faq->save();
        }

        // Update or create translation
        $faq_translation = FaqTranslation::firstOrNew([
            'locale' => $lang,
            'faq_id' => $faq->id
        ]);
        $faq_translation->question = $request->question;
        $faq_translation->answer = $request->answer;
        $faq_translation->save();

        flash(translate('FAQ has been updated successfully'))->success();
        return back();

        } catch (\Exception $e) {
            flash(translate('An error occurred while updating the FAQ: ') . $e->getMessage())->error();
            return back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     */
    public function destroy(Faq $faq)
    {
        // Delete FAQ translations
        foreach ($faq->translations as $translation) {
            $translation->delete();
        }

        $faq->delete();

        flash(translate('FAQ has been deleted successfully'))->success();
        return redirect()->route('faqs.index');
    }

    /**
     * Toggle the publication status of an FAQ
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function toggleStatus(Request $request)
    {
        $faq = Faq::findOrFail($request->id);
        $faq->is_published = $request->status;
        $faq->save();

        return response()->json(['success' => true]);
    }

    /**
     * Update the sort order of FAQs via AJAX
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'faqs' => 'required|array',
            'faqs.*.id' => 'required|exists:faqs,id',
            'faqs.*.sort_order' => 'required|integer|min:0'
        ]);

        foreach ($request->faqs as $faqData) {
            Faq::where('id', $faqData['id'])
                ->update(['sort_order' => $faqData['sort_order']]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Bulk actions for FAQs
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:publish,unpublish,delete',
            'faq_ids' => 'required|array',
            'faq_ids.*' => 'exists:faqs,id'
        ]);

        $faqs = Faq::whereIn('id', $request->faq_ids);

        switch ($request->action) {
            case 'publish':
                $faqs->update(['is_published' => true]);
                flash(translate('Selected FAQs have been published successfully'))->success();
                break;
            case 'unpublish':
                $faqs->update(['is_published' => false]);
                flash(translate('Selected FAQs have been unpublished successfully'))->success();
                break;
            case 'delete':
                // Delete translations first
                FaqTranslation::whereIn('faq_id', $request->faq_ids)->delete();
                $faqs->delete();
                flash(translate('Selected FAQs have been deleted successfully'))->success();
                break;
        }

        return redirect()->route('faqs.index');
    }

    /**
     * Duplicate an FAQ
     *
     * @param  int  $id
     */
    public function duplicate($id)
    {
        $originalFaq = Faq::with('translations')->findOrFail($id);

        // Create new FAQ
        $newFaq = $originalFaq->replicate();
        $newFaq->slug = Faq::generateUniqueSlug($originalFaq->getTranslation('question'));
        $newFaq->is_published = false; // New FAQ should be unpublished by default
        $newFaq->view_count = 0;
        $newFaq->save();

        // Duplicate translations
        foreach ($originalFaq->translations as $translation) {
            $newTranslation = $translation->replicate();
            $newTranslation->faq_id = $newFaq->id;
            $newTranslation->save();
        }

        flash(translate('FAQ has been duplicated successfully'))->success();
        return redirect()->route('faqs.edit', $newFaq->id);
    }
}
