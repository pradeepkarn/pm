<?php
class Payment_ctrl
{
     
     // List page
     public function list($req = null)
     {
         $req = obj($req);
 
         $current_page = 0;
         $data_limit = DB_ROW_LIMIT;
         $page_limit = "0,$data_limit";
         $cp = 0;
         if (isset($req->page) && intval($req->page)) {
             $cp = $req->page;
             $current_page = (abs($req->page) - 1) * $data_limit;
             $page_limit = "$current_page,$data_limit";
         }
         $total_payments = $this->payment_list(ord: "DESC", limit: 10000, active: 1);
         $tp = count($total_payments);
         if ($tp %  $data_limit == 0) {
             $tp = $tp / $data_limit;
         } else {
             $tp = floor($tp / $data_limit) + 1;
         }
         if (isset($req->search)) {
             $payment_list = $this->payment_search_list($keyword=$req->search, $ord = "DESC", $limit = $page_limit, $active = 1);
         }else{
             $payment_list = $this->payment_list(ord: "DESC", limit: $page_limit, active: 1);
         }
         $context = (object) array(
             'page' => 'payments/list.php',
             'data' => (object) array(
                 'req' => obj($req),
                 'payment_list' => $payment_list,
                 'total_page' => $tp,
                 'current_page' => $cp,
                 'is_active' => true
             )
         );
         $this->render_main($context);
     }
      // render function
    public function render_main($context = null)
    {
        import("apps/admin/layouts/admin-main.php", $context);
    }
    // payment list
    public function payment_list($ord = "DESC", $limit = 5, $active = 1, $sort_by = 'created_at')
    {
        $cntobj = new Model('payment');
        return $cntobj->filter_index(array('content_group' => 'game', 'is_active' => $active), $ord, $limit, $change_order_by_col=$sort_by);
    }
    public function payment_search_list($keyword, $ord = "DESC", $limit = 5, $active = 1)
    {
        $cntobj = new Model('payment');
        $search_arr['id'] = $keyword;
        $search_arr['unique_id'] = $keyword;
        $search_arr['amount'] = $keyword;
        return $cntobj->search(
            assoc_arr: $search_arr,
            ord: $ord,
            limit: $limit,
            whr_arr: array('content_group' => 'game', 'is_active' => $active)
        );
    }
 
}
