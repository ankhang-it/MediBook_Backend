<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = DB::table('users')->get();

        $appointmentMessages = [
            'Bạn có lịch hẹn khám bệnh vào ngày mai',
            'Lịch hẹn khám bệnh của bạn đã được xác nhận',
            'Nhắc nhở: Bạn có lịch hẹn khám bệnh trong 2 giờ tới',
            'Lịch hẹn khám bệnh đã được đặt thành công',
            'Lịch hẹn khám bệnh đã bị hủy, vui lòng đặt lại'
        ];

        $paymentMessages = [
            'Thanh toán phí khám bệnh đã được xử lý thành công',
            'Thanh toán của bạn đang được xử lý',
            'Thanh toán thất bại, vui lòng thử lại',
            'Hoàn tiền đã được xử lý',
            'Nhắc nhở thanh toán phí khám bệnh'
        ];

        $systemMessages = [
            'Chào mừng bạn đến với MediBook!',
            'Hệ thống đã được cập nhật',
            'Thông báo bảo trì hệ thống',
            'Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi',
            'Hướng dẫn sử dụng ứng dụng mới'
        ];

        $reminderMessages = [
            'Đừng quên uống thuốc theo đơn',
            'Nhắc nhở tái khám định kỳ',
            'Kiểm tra sức khỏe định kỳ',
            'Theo dõi các triệu chứng bệnh',
            'Duy trì chế độ ăn uống lành mạnh'
        ];

        $notificationTypes = ['appointment', 'payment', 'system', 'reminder'];
        $notifications = [];

        foreach ($users as $user) {
            // Create 3-8 notifications per user
            $notificationCount = rand(3, 8);

            for ($i = 0; $i < $notificationCount; $i++) {
                $type = $notificationTypes[array_rand($notificationTypes)];
                $message = '';
                $isRead = rand(0, 1); // Random read/unread status

                switch ($type) {
                    case 'appointment':
                        $message = $appointmentMessages[array_rand($appointmentMessages)];
                        break;
                    case 'payment':
                        $message = $paymentMessages[array_rand($paymentMessages)];
                        break;
                    case 'system':
                        $message = $systemMessages[array_rand($systemMessages)];
                        break;
                    case 'reminder':
                        $message = $reminderMessages[array_rand($reminderMessages)];
                        break;
                }

                $notifications[] = [
                    'notification_id' => Str::uuid(),
                    'user_id' => $user->user_id,
                    'message' => $message,
                    'type' => $type,
                    'status' => $isRead,
                    'created_at' => now()->subDays(rand(0, 30)),
                    'updated_at' => now()->subDays(rand(0, 10)),
                ];
            }
        }

        DB::table('notifications')->insert($notifications);
    }
}
