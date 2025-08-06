private function getCpfDataFromApi($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        if (empty($cpf)) {
            return [
                'TransactionResultTypeCode' => 6,
                'Message' => "'Cpf' não pode ser nulo."
            ];
        }

        $token = '268753a9b3a24819ae0f02159dee6724'; 
        $url = "https://api.exato.digital/receita-federal/cpf?token={$token}&cpf={$cpf}&format=json";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch); 
        if ($response === FALSE) {
            curl_close($ch);
            return null; 
        }

        curl_close($ch);
        return json_decode($response, true); 
    }

    private function validateCpf($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf); 

        if (strlen($cpf) !== 11) {
            return false; 
        }

        return true;
    }
}