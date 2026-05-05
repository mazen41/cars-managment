<?php

namespace App\Traits;
use App;
trait HasTranslation
{
  public function getTranslation($field = '', $lang = false)
    {
        $lang = $lang == false ? App::getLocale() : $lang;
        $translation = $this->translations->where('lang', $lang)->first();
        return $translation != null ? $translation->$field : $this->$field;
    }

    public function translate(array $attributes, array $values)
    {
        $translation = $this->translations()->where($attributes)->first();
        if($translation){
            $this->translations()->where($attributes)->update($values);
        } else{
            $this->translations()->create(array_merge($attributes, $values));
        }
    }
}
