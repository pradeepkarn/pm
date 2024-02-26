<?php 
class User_ctrl
{
    public function index($req=null)
    {
        $context = (object) array(
            'page'=>'user-dashboard.php',
            'data' => (object) array(
                'req' => obj($req),
                'page_data' => 'other_data'
            )
        );
        $this->render_main($context);
    }
    public function render_main($context=null)
    {
        import("apps/user/layouts/user-main.php",$context);
    }
}
