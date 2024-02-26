<?php 

$admin_payment_routes =[
// games
'/admin/payment/create' => 'Payment_ctrl@create@name.paymentCreate',
'/admin/payment/create/save-by-ajax' => 'Payment_ctrl@save@name.paymentStoreAjax',
'/admin/payment/list' => 'Payment_ctrl@list@name.paymentList',
'/admin/payment/trash-list' => 'Payment_ctrl@trash_list@name.paymentTrashList',
'/admin/payment/edit/{id}' => 'Payment_ctrl@edit@name.paymentEdit',
'/admin/payment/delete-more-img-ajax' => 'Payment_ctrl@delete_more_img@name.paymentDeleteMoreImgAjax',
'/admin/payment/trash/{id}' => 'Payment_ctrl@move_to_trash@name.paymentTrash',
'/admin/payment/restore/{id}' => 'Payment_ctrl@restore@name.paymentRestore',
'/admin/payment/delete/{id}' => 'Payment_ctrl@delete_trash@name.paymentDelete',
'/admin/payment/edit/{id}/save-by-ajax' => 'Payment_ctrl@update@name.paymentUpdateAjax',
'/admin/payment/toggle-marked-page' => 'Payment_ctrl@toggle_trending@name.paymentToggleMarked',
];