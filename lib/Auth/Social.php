<?php

namespace Auth;

/**
 * Authenticaion using Social
 */
trait Social
{
    /**
     * Get a social API connection
     * 
     * @param string $service
     * @return \Social\Connection
     */
    protected static function getSocialConnection($service)
    {
        $cfg = \App::config()->social;
        
        switch ($service) {
            case 'linkedin': return new Social\LinkedIn\Connection($cfg->linkedin->client_id, $cfg->linkedin->client_secret, $_SESSION);
            case 'google':   return new Social\Google\Connection($cfg->google->api_key, $cfg->google->client_id, $cfg->google->client_secret, $_SESSION);
            case 'facebook': return new Social\Facebook\Connection($cfg->facebook->client_id, $cfg->facebook->client_secret, $_SESSION);
            case 'twitter':  return new Social\Twitter\Connection($cfg->twitter->consumer_key, $cfg->twitter->consumer_secret, $_SESSION);
        }
        
        throw new Exception("Unknown service '$service'");
    }


    /**
     * Login using a social network
     * 
     * @param Social\Connection|string $conn  A Social connection supporting Auth
     * @return boolean
     */
    public static function loginWith($conn)
    {
        if (is_string($conn)) $conn = self::getSocialConnection($conn);
        
        try {
            $service = $conn::serviceProvider;
            $conn->auth(@\App::config()->social->$service->scope);
        } catch (Social\AuthException $e) {
            return false;
        }

        // User is already registered and social network is already known
        $user = User::fetch([$conn::serviceProvider . '_id'=>$conn->me()->id]);

        // User is already registered, but using a new social network
        if (!isset($user) && $conn->me()->getEmail()) {
            $user = User::fetch(['email'=>$conn->me()->getEmail()]);
        }
        
        // This is a new user
        if (!isset($user)) {
            $user = new User();
            $user->status = 'active';
        }
        
        // Add info from the social network and save
        $user->addSocialNetwork($conn)->save();
        return self::setUser($user);
    }
}
