<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => 'nullable|string|max:255',
            'redeemed_coupon_id' => 'nullable|exists:coupon,coupon_id',
            'payment_method' => 'required|in:credit_card,COD,banking',
            'total_coupon_discount' => 'required|numeric|min:0',
            'total_product_discount' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:1000',
            'point' => 'required|integer|min:0',
            'total' => 'required|numeric|min:0',
            'user_address' => 'required|string|max:500',
            'status' => 'required|in:pending,processing,delivered,refunded,canceled',
            'items' => 'required|array', // Array of products
            'items.*.product_id' => 'required|exists:products,product_id',
            'items.*.product_price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }

    public function messages()
    {
        return [
            'code.required' => 'Order code is required',
            'code.string' => 'Order code must be a string',
            'code.max' => 'Order code must not exceed 255 characters',
            'code.unique' => 'Order code must be unique',
            'redeemed_coupon_id.exists' => 'The coupon does not exist',
            'payment_method.required' => 'Payment method is required',
            'payment_method.in' => 'Invalid payment method selected',
            'total_coupon_discount.required' => 'Total coupon discount is required',
            'total_coupon_discount.numeric' => 'Total coupon discount must be a number',
            'total_product_discount.required' => 'Total product discount is required',
            'total_product_discount.numeric' => 'Total product discount must be a number',
            'note.string' => 'Note must be a string',
            'note.max' => 'Note cannot exceed 1000 characters',
            'point.required' => 'Points are required',
            'point.integer' => 'Points must be an integer',
            'point.min' => 'Points cannot be negative',
            'total.required' => 'Total amount is required',
            'total.numeric' => 'Total must be a number',
            'total.min' => 'Total cannot be negative',
            'user_address.required' => 'User address is required',
            'user_address.max' => 'User address cannot exceed 500 characters',
            'status.required' => 'Order status is required',
            'status.in' => 'Invalid order status',
        ];
    }
}
