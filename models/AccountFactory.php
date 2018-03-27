<?php

/**
 * Create new account (aka wallet)
 */
class AccountFactory
{
    const ADDRESS_VERSION = 1;
    
    /**
     * Address scheme
     * @var string 
     */
    protected $network;
    
    /**
     * Incrementing nonce
     * @var int 
     */
    protected $nonce;
    
    /**
     * Class constructor
     * 
     * @param int|string $network
     * @param int        $nonce
     */
    public function __construct($network, $nonce = 0)
    {
        $this->network = is_int($network) ? chr($network) : substr($network, 0, 1);
        $this->nonce = $nonce;
    }
    
    /**
     * Get the new nonce.
     * 
     * @return int
     */
    protected function getNonce()
    {
        return $this->nonce++;
    }
    
    /**
     * Create the account seed using several hashing algorithms.
     * 
     * @param string $seedText  Brainwallet seed string
     * @return string  raw seed (not encoded)
     */
    public function createAccountSeed($seedText)
    {
        $seedBase = pack('La*', $this->getNonce(), $seedText);
        
        $secureSeed = Keccak::hash(sodium\crypto_generichash($seedBase, null, 32), 256, true);
        $seed = hash('sha256', $secureSeed, true);
        
        return $seed;
    }
    
    /**
     * Create ED25519 sign keypairs
     * 
     * @param string $seed
     * @return object
     */
    protected function createSignKeys($seed)
    {
        $keypair = \sodium\crypto_sign_seed_keypair($seed);
        $publickey = \sodium\crypto_sign_publickey($keypair);
        $secretkey = \sodium\crypto_sign_secretkey($keypair);

        return (object)compact('publickey', 'secretkey');
    }
    
    /**
     * Create X25519 encrypt keypairs
     * 
     * @param string $seed
     * @return object
     */
    protected function createEncryptKeys($seed)
    {
        $keypair = \sodium\crypto_box_seed_keypair($seed);
        $publickey = \sodium\crypto_box_publickey($keypair);
        $secretkey = \sodium\crypto_box_secretkey($keypair);
        
        return (object)compact('publickey', 'secretkey');
    }

    /**
     * Create an address from a public key
     * 
     * @param string $publickey  Raw public key (not encoded)
     * @param string $type       Type of key 'sign' or 'encrypt'
     * @return string  raw (not encoded)
     */
    public function createAddress($publickey, $type = 'encrypt')
    {
        if ($type === 'sign') {
            $publickey = \sodium\crypto_sign_ed25519_pk_to_curve25519($publickey);
        }
        
        $publickeyHash = substr(Keccak::hash(sodium\crypto_generichash($publickey, null, 32), 256), 0, 40);
        
        $packed = pack('CaH40', self::ADDRESS_VERSION, $this->network, $publickeyHash);
        $chksum = substr(Keccak::hash(sodium\crypto_generichash($packed), 256), 0, 8);
        
        return pack('CaH40H8', self::ADDRESS_VERSION, $this->network, $publickeyHash, $chksum);
    }
    
    /**
     * Create a new account from a seed
     * 
     * @param string $seedText  Brainwallet seed string
     * @return Account
     */
    public function seed($seedText)
    {
        $seed = $this->createAccountSeed($seedText);
        
        $base58 = new StephenHill\Base58();
        $seed58 = $base58->encode($seed);
        
        $account = new Account();
        
        $account->sign = $this->createSignKeys($seed);
        $account->encrypt = $this->createEncryptKeys($seed);
        $account->address = $this->createAddress($account->encrypt->publickey, 'encrypt');
        
        return $account;
    }
    
    
    /**
     * Convert sign keys to encrypt keys.
     * 
     * @param object|string $sign
     * @return object
     */
    public function convertSignToEncrypt($sign)
    {
        $encrypt = (object)[];
        
        if (isset($sign->secretkey)) {
            $secretkey = \sodium\crypto_sign_ed25519_sk_to_curve25519($sign->secretkey);

            // Swap bits, on uneven???
            $bytes = unpack('C*', $secretkey);
            $i = count($bytes); // 1 based array
            $bytes[$i] = $bytes[$i] % 2 ? ($bytes[$i] | 0x80) & ~0x40 : $bytes[$i];
            
            $encrypt->secretkey = pack('C*', ...$bytes);
        }
        
        if (isset($sign->publickey)) {
            $encrypt->publickey = \sodium\crypto_sign_ed25519_pk_to_curve25519($sign->publickey);
        }
        
        return $encrypt;
    }
    
    /**
     * Get and verify the raw public and private key.
     * 
     * @param array  $keys
     * @param string $type  'sign' or 'encrypt'
     * @return object
     * @throws InvalidAccountException  if keys don't match
     */
    protected function calcKeys($keys, $type)
    {
        $base58 = new \StephenHill\Base58();
        
        if (!isset($keys['secretkey'])) {
            return (object)['publickey' => $base58->decode($keys['publickey'])];
        }
        
        $secretkey = $base58->decode($keys['secretkey']);
        
        $publickey = $type === 'sign' ?
            sodium\crypto_sign_publickey_from_secretkey($secretkey) :
            sodium\crypto_box_publickey_from_secretkey($secretkey);
        
        if (isset($keys['publickey']) && $base58->decode($keys['publickey']) !== $publickey) {
            throw new InvalidAccountException("Public {$type} key doesn't match private {$type} key");
        }
        
        return (object)compact('secretkey', 'publickey');
    }
    
    /**
     * Get and verify raw address.
     * 
     * @param string $address  Base58 encoded address
     * @param object $sign     Sign keys
     * @param object $encrypt  Encrypt keys
     * @return string
     * @throws InvalidAccountException  if address doesn't match
     */
    protected function calcAddress($address, $sign, $encrypt)
    {
        $addressSign = isset($sign->publickey) ? $this->createAddress($sign->publickey, 'sign') : null;
        $addressEncrypt = isset($encrypt->publickey) ? $this->createAddress($encrypt->publickey, 'encrypt') : null;
        
        if (isset($addressSign) && isset($addressEncrypt) && $addressSign !== $addressEncrypt) {
            throw new InvalidAccountException("Sign key doesn't match encrypt key");
        }
        
        if (isset($address)) {
            $base58 = new \StephenHill\Base58();
            $rawAddress = $base58->decode($address);
        
            if (
                (isset($addressSign) && $rawAddress !== $addressSign) ||
                (isset($addressEncrypt) && $rawAddress !== $addressEncrypt)
            ) {
                throw new InvalidAccountException("Address doesn't match keypair; possible network mismatch");
            }
        } else {
            $rawAddress = $addressSign ?: $addressEncrypt;
        }
        
        return $rawAddress;
    }
     
    /**
     * Create an account from base58 encoded keys.
     * 
     * @param array $data
     */
    public function create($data)
    {
        $account = new Account();
        
        $account->sign = isset($data['sign']) ? $this->calcKeys($data['sign'], 'sign') : null;
        
        $account->encrypt = isset($data['encrypt']) ?
            $this->calcKeys($data['encrypt'], 'encrypt') :
            (isset($account->sign) ? $this->convertSignToEncrypt($account->sign) : null);
        
        $address = isset($data['address']) ? $data['address'] : null;
        $account->address = $this->calcAddress($address, $account->sign, $account->encrypt);
        
        return $account;
    }
}
