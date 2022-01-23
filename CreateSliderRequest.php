<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateSliderRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'link' => 'required',
            'position' => 'required',
            'image' => 'required|mimes:jpeg,jpg,png',
            'status' => 'required',
            'title_status' => 'required',
            'link_status' => 'required',
        ];
    }

    /**
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Slider Adı Zorunludur',
            'link.required' => 'Slider Linki Zorunludur',
            'position.required' => 'Slider Pozisyonu Zorunludur',
            'image.required' => 'Slider Görseli Zorunludur',
            'image.mimes' => 'Slider Görseli JPEG, JPG veya PNG türünde olmalıdır',
        ];
    }
}
