@php
    $inspections = collect([$carInspection]);
    $sectionDataByInspection = collect([$carInspection->id => $sectionData]);
    $title = translate('Vehicle Examination Report');
@endphp

@include('backend.cars.inspections.pdf-list-report', [
    'title' => $title,
    'inspections' => $inspections,
    'sectionDataByInspection' => $sectionDataByInspection,
    'font_family' => $font_family,
    'direction' => $direction,
    'text_align' => $text_align,
    'not_text_align' => $not_text_align,
])
