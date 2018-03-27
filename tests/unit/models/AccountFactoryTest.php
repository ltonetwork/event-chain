<?php

/**
 * @covers AccountFactory
 */
class AccountFactoryTest extends \Codeception\Test\Unit
{
    public $seedText = "manage manual recall harvest series desert melt police rose hollow moral pledge kitten position add";
    
    /**
     * @see https://specs.livecontracts.io/cryptography.html#asymmetric-encryption
     */
    public function testCreateAccountSeed()
    {
        $base58 = new \StephenHill\Base58();
        
        $factory = new AccountFactory('W', 0);
        $seed = $factory->createAccountSeed($this->seedText);
        
        $this->assertEquals("49mgaSSVQw6tDoZrHSr9rFySgHHXwgQbCRwFssboVLWX", $base58->encode($seed));
    }

    
    public function createAddressProvider()
    {
        return [
            [ "3PPbMwqLtwBGcJrTA5whqJfY95GqnNnFMDX", 'W' ],
            [ "3PPbMwqLtwBGcJrTA5whqJfY95GqnNnFMDX", 0x57 ],
            [ "3NBaYzWT2odsyrZ2u1ghsrHinBm4xFRAgLX", 'T' ],
            [ "3NBaYzWT2odsyrZ2u1ghsrHinBm4xFRAgLX", 0x54 ],
        ];
    }
    
    /**
     * @dataProvider createAddressProvider
     * 
     * @param string     $expected
     * @param string|int $network
     */
    public function testCreateAddressEncrypt($expected, $network)
    {
        $base58 = new \StephenHill\Base58();

        $factory = new AccountFactory($network, 0);
        
        $publickey = $base58->decode("HBqhfdFASRQ5eBBpu2y6c6KKi1az6bMx8v1JxX4iW1Q8");
        $address = $factory->createAddress($publickey, "encrypt");
        
        $this->assertEquals($expected, $base58->encode($address));
    }
    
    /**
     * @dataProvider createAddressProvider
     * 
     * @param string     $expected
     * @param string|int $network
     */
    public function testCreateAddressSign($expected, $network)
    {
        $base58 = new \StephenHill\Base58();

        $factory = new AccountFactory($network, 0);
        
        $publickey = $base58->decode("BvEdG3ATxtmkbCVj9k2yvh3s6ooktBoSmyp8xwDqCQHp");
        $address = $factory->createAddress($publickey, "sign");
        
        $this->assertEquals($expected, $base58->encode($address));
    }

    public function convertSignToEncryptProvider()
    {
        return [
            [
                (object)['publickey' => "EZa2ndj6h95m3xm7DxPQhrtANvhymNC7nWQ3o1vmDJ4x"],
                (object)['publickey' => "gVVExGUK4J5BsxwUfYsFkkjpn6A7BcvYdmARL28GBRc"]
            ],
            [
                (object)['publickey' => "BVv1ZuE3gKFa6krwWJQwEmrLYUESuUabNCXgYTmCoBt6"],
                (object)['publickey' => "FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y"]
            ],
            [
                (object)['publickey' => "4Xpf8guEGD3ZnRJLuEu8JjpmKnHpXR49mFE4Zm9m9P1z"],
                (object)['publickey' => "96yeNG1KYJKAVnfKqfkfktkXuPj1CLPEsgCDkm42VcaT"]
            ],
            [
                (object)['publickey' => "Efv4wPdjfyVNvbp21xwiTXnirQti7jJy56W9doDVzfhG"],
                (object)['publickey' => "7TecQdLbPuxt3mWukbZ1g1dTZeA6rxgjMxfS9MRURaEP"]
            ],
            [
                (object)['secretkey' => "ACsYcMff8UPUc5dvuCMAkqZxcRTjXHMnCc29TZkWLQsZ"],
                (object)['secretkey' => "5DteGKYVUUSSaruCK6H8tpd4oYWfcyNohyhJiYGYGBVzhuEmAmRRNcUJQzA2bk4DqqbtpaE51HTD1i3keTvtbCTL"]
            ],
            [
                (object)['secretkey' => "BnjFJJarge15FiqcxrB7Mzt68nseBXXR4LQ54qFBsWJN"],
                (object)['secretkey' => "wJ4WH8dD88fSkNdFQRjaAhjFUZzZhV5yiDLDwNUnp6bYwRXrvWV8MJhQ9HL9uqMDG1n7XpTGZx7PafqaayQV8Rp"]
            ]
        ];
    }
    
    /**
     * @dataProvider convertSignToEncryptProvider
     * 
     * @param object $expected
     * @param object $sign
     */
    public function testConvertSignToEncrypt($expected, $sign)
    {
        $base58 = new \StephenHill\Base58();
        
        foreach ($sign as &$value) {
            $value = $base58->decode($value);
        }
        
        $factory = new AccountFactory('W', 0);

        $encrypt = $factory->convertSignToEncrypt($sign);
        
        foreach ($encrypt as &$value) {
            $value = $base58->encode($value);
        }
        
        $this->assertEquals($expected, $encrypt);
    }
    
    
    public function testSeed()
    {
        $base58 = new \StephenHill\Base58();
        
        $factory = new AccountFactory('W', 0);
        
        $account = $factory->seed($this->seedText);
        
        $this->assertInstanceOf(Account::class, $account);
        
        $this->assertEquals("BvEdG3ATxtmkbCVj9k2yvh3s6ooktBoSmyp8xwDqCQHp", $base58->encode($account->sign->publickey));
        $this->assertEquals("pLX2GgWzkjiiPp2SsowyyHZKrF4thkq1oDLD7tqBpYDwfMvRsPANMutwRvTVZHrw8VzsKjiN8EfdGA9M84smoEz",
            $base58->encode($account->sign->secretkey));
        
        $this->assertEquals("HBqhfdFASRQ5eBBpu2y6c6KKi1az6bMx8v1JxX4iW1Q8", $base58->encode($account->encrypt->publickey));
        $this->assertEquals("3kMEhU5z3v8bmer1ERFUUhW58Dtuhyo9hE5vrhjqAWYT", $base58->encode($account->encrypt->secretkey));
        
        $this->assertEquals("3PPbMwqLtwBGcJrTA5whqJfY95GqnNnFMDX", $base58->encode($account->address));
    }

    
    public function createSecretProvider()
    {
        $sign = [
            'secretkey' => 'wJ4WH8dD88fSkNdFQRjaAhjFUZzZhV5yiDLDwNUnp6bYwRXrvWV8MJhQ9HL9uqMDG1n7XpTGZx7PafqaayQV8Rp',
            'publickey' => 'FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y'
        ];
        $signSecret = ['secretkey' => $sign['secretkey']];
        
        $encrypt = [
            'secretkey' => 'BnjFJJarge15FiqcxrB7Mzt68nseBXXR4LQ54qFBsWJN',
            'publickey' => 'BVv1ZuE3gKFa6krwWJQwEmrLYUESuUabNCXgYTmCoBt6'
        ];
        $encryptSecret = ['secretkey' => $encrypt['secretkey']];
        
        $address = '3PLSsSDUn3kZdGe8qWEDak9y8oAjLVecXV1';
        
        return [
            [ compact('sign', 'encrypt', 'address'), true, true ],
            [ compact('sign', 'encrypt'), true, true ],
            [ compact('sign', 'address'), true, true ],
            [ compact('sign'), true, true ],
            [ compact('encrypt', 'address'), false, true ],
            [ compact('encrypt'), false, true ],
            [ compact('address'), false, false ],
            [ ['sign' => $signSecret, 'encrypt' => $encryptSecret, 'address' => $address], true, true ],
            [ ['sign' => $signSecret, 'encrypt' => $encryptSecret], true, true ],
            [ ['sign' => $signSecret], true, true ],
            [ ['encrypt' => $encryptSecret], false, true ]
        ];
    }
    
    /**
     * @dataProvider createSecretProvider
     * 
     * @param array   $data
     * @param boolean $hasSign
     * @param boolean $hasEncrypt
     */
    public function testCreateFull(array $data, $hasSign, $hasEncrypt)
    {
        $this->markAsRisky();
        $base58 = new \StephenHill\Base58();
        
        $factory = new AccountFactory('W', 0);
        
        $account = $factory->create($data);
        
        $this->assertInstanceOf(Account::class, $account);

        if ($hasSign) {
            $this->assertEquals("wJ4WH8dD88fSkNdFQRjaAhjFUZzZhV5yiDLDwNUnp6bYwRXrvWV8MJhQ9HL9uqMDG1n7XpTGZx7PafqaayQV8Rp",
                $base58->encode($account->sign->secretkey));
            $this->assertEquals("FkU1XyfrCftc4pQKXCrrDyRLSnifX1SMvmx1CYiiyB3Y", $base58->encode($account->sign->publickey));
        } else {
            $this->assertNull($account->sign);
        }
        
        if ($hasEncrypt) {
            $this->assertEquals("BnjFJJarge15FiqcxrB7Mzt68nseBXXR4LQ54qFBsWJN", $base58->encode($account->encrypt->secretkey));
            $this->assertEquals("BVv1ZuE3gKFa6krwWJQwEmrLYUESuUabNCXgYTmCoBt6", $base58->encode($account->encrypt->publickey));
        } else {
            $this->assertNull($account->encrypt);
        }
        
        $this->assertEquals("3PLSsSDUn3kZdGe8qWEDak9y8oAjLVecXV1", $base58->encode($account->address));
    }
}
