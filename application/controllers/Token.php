<?php

class Token extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MAccurate');
    }

    public function get()
    {
        $this->output->set_content_type('application/json');

        $accurate = $this->MAccurate->get();

        $token = $accurate['api_token'];
        $signature_secret = $accurate['signature_secret'];
        $timestamp = date("d/m/Y H:i:s");

        $hash = hash_hmac('sha256', $timestamp, $signature_secret);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://account.accurate.id/api/api-token.do',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $token,
                'X-Api-Timestamp: ' . $timestamp,
                'X-Api-Signature: ' . $hash
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        // echo $response;

        $result = [
            'code' => 200,
            'status' => 'failed',
            'msg' => 'Tidak ada data',
            'detail' => json_decode($response, true)
        ];

        $this->output->set_output(json_encode($result));
    }
}
