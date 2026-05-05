@php
    $value = null;
    for ($i=0; $i < $child_category->level; $i++){
        $value .= '--';
    }
@endphp
<option value="{{ $child_category->id }}" @if(isset($selected_id) && $selected_id == $child_category->id) selected @endif>{{ $value." ".$child_category->getTranslation('name') }}</option>
@if ($child_category->categories)
    @foreach ($child_category->categories as $childCategory)
        @include('categories.child_category', ['child_category' => $childCategory, 'selected_id' => $selected_id ?? null])
    @endforeach
@endif
