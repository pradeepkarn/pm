<?php
class Fuels_api
{
    public $db;
    function __construct()
    {
        $this->db = (new DB_ctrl)->db;
    }
    function get_fules($req = null)
    {
        header('Content-Type: application/json');
        $req = obj($req);
        $data  = json_decode(file_get_contents('php://input'));
        $rules = [
            'token' => 'required|string'
        ];
        $pass = validateData(data: arr($data), rules: $rules);
        if (!$pass) {
            $api['success'] = false;
            $api['data'] = null;
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        }
        $user = false;
        $user = (new Users_api)->get_user_by_token($data->token);
        if ($user) {
            if ($user['user_group'] != 'driver') {
                msg_set("Invalid login portal");
                $api['success'] = false;
                $api['data'] = null;
                $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
                echo json_encode($api);
                exit;
            }
            try {
                $dt = $this->fuel_list($driver_id = $user['id']);
                msg_set(count($dt) ? "Fuels found" : "Fuels not found");
                $api['success'] = count($dt) ? true : false;
                $api['data'] = count($dt) ? $dt : null;
                $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
                echo json_encode($api);
                exit;
            } catch (PDOException $th) {
                msg_set("Unable to fetch");
                $api['success'] = false;
                $api['data'] = null;
                $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
                echo json_encode($api);
                exit;
            }
        } else {
            msg_set("User not found, invalid token");
            $api['success'] = false;
            $api['data'] = null;
            $api['msg'] = msg_ssn(return: true, lnbrk: ", ");
            echo json_encode($api);
            exit;
        }
    }
    function fuel_list($driver_id)
    {
        $arr = [];
        $arr['net_volume'] = 0;
        $arr['unit'] = 'litre';
        $data = $this->db->show("select id, volume, unit, fuel_group as fuel_type, balance, created_at from fuels where user_id = '$driver_id'");
        foreach ($data as $key => $d) {
            $d['balance'] = strval($d['balance'])=="1"?"added":"deducted";
            $d['created_at'] = strtotime($d['created_at']);
            if (strval($d['balance'])=="added") {
                $arr['net_volume'] += floatval($d['volume']);
            }
            if (strval($d['balance'])=="deducted"){
                $arr['net_volume'] -= floatval($d['volume']);
            }
            $arr['fuels'][] = $d;
        }
        return $arr;
    }
}
