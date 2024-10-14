<?php

class Timbang extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MAccurate');
    }

    public function get()
    {
        $this->output->set_content_type('application/json');

        $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

        $accurate = $this->MAccurate->get();

        $itemNo = $post['itemNo'];
        $no_batch = $post['no_batch'];
        $qty = $post['qty'];

        $token = $accurate['api_token'];
        $signature_secret = $accurate['signature_secret'];
        $timestamp = date("d/m/Y H:i:s");
        // $signature_secret = "31d49b3dc632614495ff8071e5be44a1";
        // $timestamp = "02/11/2023 09:01:01";

        $hash = base64_encode(hash_hmac('sha256', $timestamp, $signature_secret, true));

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://zeus.accurate.id/accurate/api/item-adjustment/save.do',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
                "adjustmentAccountNo":"",
                "detailItem": [
                    {
                        "itemAdjustmentType":"ADJUSTMENT_OUT",
                        "itemNo":"' . $itemNo . '",
                        "quantity":"' . $qty . '"
                    }
                ],
                "transDate": "' . date('d/m/Y') . '"
            }',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $token,
                'X-Api-Timestamp: ' . $timestamp,
                'X-Api-Signature: ' . $hash,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        // echo $response;

        $result = [
            'code' => 200,
            'status' => 'ok',
            'msg' => 'Data found',
            'detail' => json_decode($response, true)
        ];

        $this->output->set_output(json_encode($result));
    }
}
