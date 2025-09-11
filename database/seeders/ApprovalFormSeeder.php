<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ApprovalForm;
use App\Models\Department;

class ApprovalFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy danh sách phòng ban từ database
        $departments = Department::all()->map(function($dept) {
            return [
                'value' => $dept->id,
                'label' => $dept->name
            ];
        })->toArray();

        $forms = [
            [
                'form_type' => 'payment_request',
                'form_name' => 'Đề xuất thanh toán',
                'description' => 'Form đề xuất thanh toán cho các khoản chi phí',
                'form_fields' => [
                    [
                        'name' => 'title',
                        'label' => 'Tiêu đề đề xuất',
                        'type' => 'text',
                        'required' => true,
                        'validation' => 'max:255'
                    ],
                    [
                        'name' => 'amount',
                        'label' => 'Số tiền',
                        'type' => 'number',
                        'required' => true,
                        'validation' => 'min:0',
                        'step' => 1000000
                    ],
                    [
                        'name' => 'reason',
                        'label' => 'Mô tả',
                        'type' => 'textarea',
                        'required' => true,
                        'validation' => 'max:500'
                    ],
                    [
                        'name' => 'department',
                        'label' => 'Phòng ban',
                        'type' => 'select',
                        'required' => true,
                        'options' => $departments
                    ],
                    [
                        'name' => 'payment_method',
                        'label' => 'Phương thức thanh toán',
                        'type' => 'select',
                        'required' => false,
                        'options' => [
                            ['value' => 'bank_transfer', 'label' => 'Chuyển khoản'],
                            ['value' => 'cash', 'label' => 'Tiền mặt'],
                            // ['value' => 'check', 'label' => 'Séc']
                        ]
                    ],
                    [
                        'name' => 'approver_select',
                        'label' => 'Người phê duyệt',
                        'type' => 'approver_select',
                        'required' => true
                    ],
                    [
                        'name' => 'items_table',
                        'label' => 'Bảng chi tiết',
                        'type' => 'dynamic_table',
                        'required' => false,
                        'columns' => [
                            ['name' => 'stt', 'label' => 'STT', 'type' => 'text', 'width' => '10%'],
                            ['name' => 'ten_hang', 'label' => 'Tên hàng hóa', 'type' => 'text', 'width' => '25%'],
                            ['name' => 'so_luong', 'label' => 'Số lượng', 'type' => 'text', 'width' => '15%'],
                            ['name' => 'thanh_tien', 'label' => 'Thành tiền', 'type' => 'text', 'width' => '15%'],
                            ['name' => 'noi_mua', 'label' => 'Nơi mua', 'type' => 'text', 'width' => '20%'],
                            ['name' => 'ghi_chu', 'label' => 'Ghi chú', 'type' => 'text', 'width' => '15%']
                        ]
                    ]
                ],
                'approval_workflow' => [
                    ['role' => 'manager', 'order' => 1, 'required' => true],
                    ['role' => 'director', 'order' => 2, 'required' => true]
                ],
                'is_active' => true
            ],
            [
                'form_type' => 'purchase_request',
                'form_name' => 'Đề xuất mua hàng',
                'description' => 'Form đề xuất mua sắm thiết bị, vật tư',
                'form_fields' => [
                    [
                        'name' => 'title',
                        'label' => 'Tiêu đề đề xuất',
                        'type' => 'text',
                        'required' => true,
                        'validation' => 'max:255'
                    ],
                    [
                        'name' => 'item_name',
                        'label' => 'Tên sản phẩm',
                        'type' => 'text',
                        'required' => true,
                        'validation' => 'max:255'
                    ],
                    [
                        'name' => 'quantity',
                        'label' => 'Số lượng',
                        'type' => 'number',
                        'required' => true,
                        'validation' => 'min:1'
                    ],
                    [
                        'name' => 'unit_price',
                        'label' => 'Đơn giá',
                        'type' => 'number',
                        'required' => true,
                        'validation' => 'min:0'
                    ],
                    [
                        'name' => 'supplier',
                        'label' => 'Nhà cung cấp',
                        'type' => 'text',
                        'required' => true,
                        'validation' => 'max:255'
                    ],
                ],
                'approval_workflow' => [
                    ['role' => 'manager', 'order' => 1, 'required' => true],
                    ['role' => 'director', 'order' => 2, 'required' => true]
                ],
                'is_active' => true
            ],
            [
                'form_type' => 'advance_request',
                'form_name' => 'Đề xuất ứng trước',
                'description' => 'Form đề xuất ứng trước tiền lương',
                'form_fields' => [
                    [
                        'name' => 'title',
                        'label' => 'Tiêu đề đề xuất',
                        'type' => 'text',
                        'required' => true,
                        'validation' => 'max:255'
                    ],
                    [
                        'name' => 'amount',
                        'label' => 'Số tiền ứng',
                        'type' => 'number',
                        'required' => true,
                        'validation' => 'min:0'
                    ],
                    [
                        'name' => 'purpose',
                        'label' => 'Mục đích sử dụng',
                        'type' => 'textarea',
                        'required' => true,
                        'validation' => 'max:500'
                    ],
                    [
                        'name' => 'repayment_plan',
                        'label' => 'Kế hoạch hoàn trả',
                        'type' => 'text',
                        'required' => true,
                        'validation' => 'max:255'
                    ],
                    [
                        'name' => 'repayment_date',
                        'label' => 'Ngày hoàn trả dự kiến',
                        'type' => 'date',
                        'required' => true
                    ],
                ],
                'approval_workflow' => [
                    ['role' => 'manager', 'order' => 1, 'required' => true],
                    ['role' => 'director', 'order' => 2, 'required' => true]
                ],
                'is_active' => true
            ]
        ];

        foreach ($forms as $form) {
            ApprovalForm::create($form);
        }
    }
}
