<?php
/**
 * SendgridBase Service
 *
 * @author: tuanha
 */
namespace SendgridBuddy;

use Exception;
use GuzzleHttp\Client;

class SendgridBase
{
    /**
     * @var GuzzleHttp\Client $client
     */
    protected $client;

    /**
     * Create instance
     */
    public function __construct()
    {
        $this->client = new Client([
            //'verify' => false, // This is not recommended settings => used for temporarily fixing cURL error 60: SSL cert problem: cert has expired
            'base_uri' => $_ENV['SENDGRID_API_BASE_URL'],
            'headers' => [
                'Authorization' => 'Bearer ' . $_ENV['SENDGRID_API_KEY'],
                'Content-Type' => 'application/json'
            ]
        ]);
    }
    
    public function getSubUsers($getAll = false, $page = 1, $limit = 1000)
    {
        if ($getAll) {
            $page = 1;
            $allSubUsers = [];
            do {
                $subUsers = $this->getSubUsers(false, $page, 1000);
                if (empty($subUsers)) {
                    break;
                } else {
                    $allSubUsers = array_merge($allSubUsers, $subUsers);
                }
                ++$page;
            } while (!empty($subUsers));
            return $allSubUsers;
        } else {
            $offset = ($page - 1) * $limit;
            $url = "subusers?limit=$limit&offset=$offset";
            try {
                $res = $this->client->request('GET', $url);
                $data = json_decode($res->getBody()->getContents(), true);
                if ($res->getStatusCode() == '200') {
                    return $data;
                } else {
                    return false;
                }
            } catch (Exception $e) {
                return false;
            }
        }
    }

    public function getParentAccountAssignedIPs()
    {
        $url = "ips/assigned";
        try {
            $res = $this->client->request('GET', $url);
            $data = json_decode($res->getBody()->getContents(), true);
            if ($res->getStatusCode() == '200') {
                return $data;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function getSubUsersOnAssignedIP($getAll = false, $ip = '', $afterKey = 0, $limit = 100)
    {
        if ($getAll) {
            $allSubUsers = [];
            do {
                $data = $this->getSubUsersOnAssignedIP(false, $ip, $afterKey);
                $afterKey = $data['_metadata']['next_params']['after_key'];
                $subUsers = $data['result'];
                $allSubUsers = array_merge($allSubUsers, $subUsers);
            } while (!is_null($afterKey));
            return $allSubUsers;
        } else {
            $url = "send_ips/ips/$ip/subusers?limit=$limit&after_key=$afterKey";
            try {
                $res = $this->client->request('GET', $url);
                $data = json_decode($res->getBody()->getContents(), true);
                if ($res->getStatusCode() == '200') {
                    return $data;
                } else {
                    return false;
                }
            } catch (Exception $e) {
                return false;
            }
        }
    }
}
