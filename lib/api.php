<?php

class CyberAPI
{
    private $adminUser;
    private $adminPassword;
    private $hostname;
    private $port;

    public function __construct($adminUser, $adminPassword, $hostname, $port = 8090)
    {
        $this->adminUser = $adminUser;
        $this->adminPassword = $adminPassword;
        $this->hostname = $hostname;
        $this->port = $port;
    }

    private function call($command, $post = [])
    {
        $ch = curl_init();
        $url = 'https://' . $this->hostname . ':' . $this->port . '/api/' . $command;

        CE_Lib::log(4, 'CyberPanel Request to: ' . $url);

        $post = array_merge([
            'adminUser' => $this->adminUser,
            'adminPass' => $this->adminPassword,
        ], $post);

        CE_Lib::log(4, 'Post Params: ');
        CE_Lib::log(4, $post);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
        $caPathOrFile = \Composer\CaBundle\CaBundle::getSystemCaRootBundlePath();
        if (is_dir($caPathOrFile)) {
            curl_setopt($ch, CURLOPT_CAPATH, $caPathOrFile);
        } else {
            curl_setopt($ch, CURLOPT_CAINFO, $caPathOrFile);
        }

        $result = curl_exec($ch);
        CE_Lib::log(4, 'CyberPanel Response: ');
        CE_Lib::log(4, $result);


        if (curl_errno($ch)) {
            throw new CE_Exception('Connection error: ' . curl_error($ch));
        }

        curl_close($ch);
        $result = json_decode($result, true);
        if (isset($result['error_message']) && $result['error_message'] != '' && $result['error_message'] != 'None') {
            throw new CE_Exception($result['error_message']);
        }
        return $result;
    }

    public function createAccount($domainName, $email, $packageName, $username, $password, $acl)
    {
        $params = [
            'domainName' => $domainName,
            'ownerEmail' => $email,
            'packageName' => $packageName,
            'websiteOwner' => $username,
            'ownerPassword' => $password,
            'acl' => $acl,
        ];
        $result = $this->call('createWebsite', $params);
        return $result;
    }

    public function changeAccountStatus($domainName, $status)
    {
        $params = [
            'websiteName' => $domainName,
            'state' => $status,
        ];
        $result = $this->call('submitWebsiteStatus', $params);
        return $result;
    }

    public function verifyConnection()
    {
        $result = $this->call('verifyConn');
        return $result;
    }

    public function terminateAccount($domainName)
    {
        $params = [
            'domainName' => $domainName
        ];
        $result = $this->call('deleteWebsite', $params);
        return $result;
    }

    public function changeAccountPassword($username, $password)
    {
        $params = [
            'websiteOwner' => $username,
            'ownerPassword' => $password
        ];
        $result = $this->call('changeUserPassAPI', $params);
        return $result;
    }

    public function changeAccountPackage($domainName, $packageName)
    {
        $url = 'changePackageAPI';
        $params = [
            'websiteName' => $domainName,
            'packageName' => $packageName
        ];
        $result = $this->call('changePackageAPI', $params);
        return $result;
    }
}
