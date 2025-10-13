<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JudgeAssignmentIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // La autorización se maneja en el middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'etapa_id' => ['nullable', 'integer', 'exists:etapas,id'],
            'tipo_eval' => ['nullable', 'string', 'in:escrita,oral'],
            'estado' => ['nullable', 'string', 'in:pending,completed'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'etapa_id.integer' => 'El ID de etapa debe ser un número entero',
            'etapa_id.exists' => 'La etapa especificada no existe',
            'tipo_eval.in' => 'El tipo de evaluación debe ser "escrita" u "oral"',
            'estado.in' => 'El estado debe ser "pending" o "completed"',
            'page.integer' => 'El número de página debe ser un entero',
            'page.min' => 'El número de página debe ser mayor o igual a 1',
        ];
    }
}
