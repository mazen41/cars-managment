

INSERT INTO `permissions` (`name`,`section`,`guard_name`,`created_at`,`updated_at`) VALUES
('view_all_external_orders','external_order','web',current_timestamp(),current_timestamp()),
('view_external_order_details','external_order','web',current_timestamp(),current_timestamp()),
('delete_external_order','external_order','web',current_timestamp(),current_timestamp()),
('update_external_order_payment_status','external_order','web',current_timestamp(),current_timestamp()),
('update_external_order_delivery_status','external_order','web',current_timestamp(),current_timestamp()),
('export_external_order','external_order','web',current_timestamp(),current_timestamp());

INSERT INTO `notification_types` (`user_type`,`type`,`name`,`default_text`, `status`,`created_at`, `updated_at`) VALUES
('admin','order_shipping_admin','يتم شحن الطلب','تم تحديد الطلب رقم [[order_code]] جاري الشحن', 1, current_timestamp(),current_timestamp()),
('admin','order_in_storage_admin','الطلب في المخزن','تم تحديد الطلب رقم [[order_code]] في المخزن', 1, current_timestamp(),current_timestamp()),
('customer','order_shipping_customer','يتم شحن الطلب','طلبك رقم [[order_code]] يتم شحنة', 1, current_timestamp(),current_timestamp()),
('customer','order_in_storage_customer','الطلب في المخزن','طلبك رقم [[order_code]] في المخزن', 1, current_timestamp(),current_timestamp());
