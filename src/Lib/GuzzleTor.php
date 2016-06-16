<?php
namespace Aszone\Component\SearchHacking\Lib;
//use GuzzleHttp\Exception\GuzzleException;
//use Psr\Http\Message\RequestInterface;
const TOR_OK = 250;

class GuzzleTor
{
    /**
     * This middleware allows to use Tor client as a proxy
     *
     * @param string $proxy Tor socks5 proxy host:port
     * @param string $torControl Tor control host:port
     * @return callable
     */
    public static function tor($options=array(),$proxy = '127.0.0.1:9050', $torControl = '127.0.0.1:9051')
    {

        if (@$options['tor_new_identity']) {
            try {
                self::requireNewTorIdentity($torControl, $options);
            } catch (\Exception $e) {
                if (@$options['tor_new_identity_exception']) {
                    throw $e;
                }
            }
        }
        $result['proxy']=$proxy;
        $result['torControl']=$torControl;
        $result['options']=$options;
        return $result;

    }


    private static function requireNewTorIdentity($torControl, $options)
    {
        list($ip, $port) = explode(':', $torControl);
        $password = @$options['tor_control_password']     ?: '';
        $timeout  = @$options['tor_new_identity_timeout'] ?: null;
        $sleep    = @$options['tor_new_identity_sleep']   ?: 0;
        $socket = @fsockopen($ip, $port, $errNo, $errStr, 60    );

        if (!$socket) {
            //throw new TorNewIdentityException("Could not connect to Tor client on $torControl: $errNo $errStr");
            exit("Could not connect to Tor client on $torControl : $errNo $errStr");
        }
        fputs($socket, "AUTHENTICATE $password\r\n");
        $response = fread($socket, 1024);
        $code = explode(' ', $response, 2)[0];
        if (TOR_OK != $code) {
            throw new TorNewIdentityException("Could not authenticate on Tor client");
        }
        fputs($socket, "signal NEWNYM\r\n");
        $response = fread($socket, 1024);
        $code = explode(' ', $response, 2)[0];
        if (TOR_OK != $code) {
            throw new TorNewIdentityException("Could not get new identity");
        }
        fclose($socket);
        if ($sleep) {
            sleep($sleep);
        }
    }


}