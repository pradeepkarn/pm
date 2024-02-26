<?php
class Review_ctrl
{
    function add_review_ajax($req = null)
    {
        $req = obj($req);
        $data = null;
        $data = $_POST;
        $rules = [
            'name_of_user' => 'required|string',
            'content_id' => 'required|integer',
            'review_message' => 'required|string',
        ];

        $pass = validateData(data: $data, rules: $rules);
        if (!$pass) {
            $data['msg'] = msg_ssn(return: true, lnbrk: "<br>");
            $data['success'] = false;
            $data['data'] = null;
            echo json_encode($data);
            exit;
        }
        $data = null;
        if (is_superuser()) {
            $db = new Dbobjects;
            $db->tableName = "review";
            $arr['name'] = $_POST['name_of_user'];
            $arr['message'] = $_POST['review_message'];
            $arr['rating'] = intval($_POST['star_point']);
            $arr['email'] = generate_dummy_email('usr');
            $arr['item_id'] = $_POST['content_id'];
            $arr['item_group'] = $req->rg;
            $arr['status'] = "published";
            $db->insertData = $arr;
            try {
                $db->create();
                msg_set("Review added");
                $data['msg'] = msg_ssn(return: true, lnbrk: "<br>");
                $data['success'] = true;
                $data['data'] = null;
                echo json_encode($data);
                exit;
            } catch (PDOException $th) {
                msg_set("Review not added");
                $data['msg'] = msg_ssn(return: true, lnbrk: "<br>");
                $data['success'] = false;
                $data['data'] = null;
                echo json_encode($data);
                exit;
            }
        } else {
            msg_set("Not authorised user to add review");
            $data['msg'] = msg_ssn(return: true, lnbrk: "<br>");
            $data['success'] = false;
            $data['data'] = null;
            echo json_encode($data);
            exit;
        }
    }
    function delete_review_ajax($req=null) {
        $req = obj($req);
        if (is_superuser()) {
            $repl = (new Model('review'))->destroy($_POST['dm_review_id']);
            if ($repl) {
                msg_set("Review deleted");
                $data['msg'] = msg_ssn(return: true, lnbrk: "<br>");
                $data['success'] = true;
                $data['data'] = null;
                echo json_encode($data);
                exit;
            }else{
                msg_set("Review not added");
                $data['msg'] = msg_ssn(return: true, lnbrk: "<br>");
                $data['success'] = false;
                $data['data'] = null;
                echo json_encode($data);
                exit;
            }
        };
    }
}
