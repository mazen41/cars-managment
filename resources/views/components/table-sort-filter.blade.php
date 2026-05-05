@props([
    'name' => 'sort',
    'options' => [
        'newest' => 'Newest',
        'oldest' => 'Oldest',
    ],
    'placeholder' => 'Sort By',
    'selected' => request('sort', 'newest'),
    'onchange' => 'sort_customers(this)'
])

<div class="col-md-2">
    <div class="form-group mb-0">
        <select
            class="form-control form-control-sm aiz-selectpicker"
            name="{{ $name }}"
            onchange="{{ $onchange }}"
        >
            <option value="">{{ translate($placeholder) }}</option>
            @foreach($options as $value => $label)
                <option value="{{ $value }}" @selected($selected == $value)>
                    {{ translate($label) }}
                </option>
            @endforeach
        </select>
    </div>
</div>
