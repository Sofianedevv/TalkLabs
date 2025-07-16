<?php

namespace App\Service;

use App\Entity\Accounts;
use Doctrine\ORM\EntityManagerInterface;


class TwoFactorService {

    private string $chiffKey;
    private EntityManagerInterface $em;

public function __construct(string $chiffKey, EntityManagerInterface $em) {

    $this->chiffKey = hex2bin($chiffKey);
    $this->em = $em;
}
 

    public function generateSecretKey() : string {

        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secretKey='';

        for($i = 0; $i< 16; $i++) {
            $secretKey .= $chars[random_int(0, 31)];
        }
        return $secretKey;
    }

    public function generateOtpAuthUrl(Accounts $user, string $secret) : string {

        $issuer = urlencode("TalksLabs");
        $accountName = urlencode($user->getUsername());

        return "otpauth://totp/{$issuer}:{$accountName}?secret={$secret}&issuer={$issuer}&digits=6&algorithm=SHA1&period=30";
    }

    public function verifyTotpCode(string $secret, string $totpCode) : bool {
        $timestamp = floor(time() / 30);
        for($i = -1; $i <= 1; $i++) {
            $code = $this->generateCode($secret, $timestamp + $i);
            if(hash_equals($code, $totpCode)) {
                return true;
            }
        }
        return false;
    }

    private function generateCode(string $secret, int $timestamp): string {
        $secretKey = $this->base32Decode($secret);
        $time = chr(0).chr(0).chr(0).chr(0).pack('N*', $timestamp);
        $hmac = hash_hmac('sha1', $time, $secretKey, true);
        $offset= ord($hmac[19]) & 0x0F;
        $hash = substr($hmac, $offset, 4);
        $code = unpack('N', $hash)[1] & 0x7FFFFFFF;
        $code %= 1000000;

        return str_pad($code, 6, '0', STR_PAD_LEFT);
    }

    private function base32Decode(string $secret) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binary ='';

        foreach (str_split($secret) as $char) {
            $index = strpos($chars, $char);
            if ($index !== false) {
                $binary .= sprintf('%05b', $index);
            }
        }

        $decode = '';
        for($i = 0; $i < strlen($binary); $i += 8) {
            $byte = substr($binary, $i, 8);
            if(strlen($byte) < 8) {
                break;
            }
            $decode .= chr(bindec($byte));
        }
        return $decode;

    }

    public function encryptSecret(string $secret): string {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($secret, 'aes-256-cbc', $this->chiffKey, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $encrypted);
    }

    
    public function decryptSecret(string $secretEncrypt): string {
        $secretDecode = base64_decode($secretEncrypt);
        $iv = substr($secretDecode, 0, 16);
        $cipher = substr($secretDecode, 16);
        return openssl_decrypt($cipher, 'aes-256-cbc', $this->chiffKey, OPENSSL_RAW_DATA, $iv);
    }

    public function enable2FA(Accounts $user, string $secret) {
        $encryptedSecret = $this->encryptSecret($secret);
        $user->setTotpSecret($encryptedSecret);
        $user->setIsTwoFactorEnabled(true);

        $this->em->persist($user);
        $this->em->flush();
    }

    public function disable2FA(Accounts $user) {

        $user->setTotpSecret(null);
        $user->setIsTwoFactorEnabled(false);

        $this->em->persist($user);
        $this->em->flush();
    }

    public function getTotpSecret(Accounts $user): ?string {
        $encryptedSecret = $user->getTotpSecret();
        if($encryptedSecret === null) {
            return null;
        }
        return $this->decryptSecret($encryptedSecret);
    }

    public function validateTotpCodeAfterLogin(Accounts $user, string $code) : bool {
      
        if(!$user->isTwoFactorEnabled()) {
            return false;
        }

        $secret = $this->getTotpSecret($user); 
            if($secret === null) {
                return false;
            }

        return $this->verifyTotpCode($secret, $code);
    }
}