<?php declare(strict_types=1);

/**
 * Map resource uri to url.
 * The mapping is configured, but may contain wildcards. This service resolves the mapping.
 */
class ResourceMapping
{
    /**
     * @var array
     */
    protected $endpoints;


    /**
     * Class constructor
     *
     * @param array $endpoints
     */
    public function __construct(array $endpoints)
    {
        $this->endpoints = $endpoints;
    }


    /**
     * Try to get an URL from a URI
     *
     * @param string $uri
     * @return string|null
     */
    protected function findURL(string $uri): ?string
    {
        $url = null;
        $uriBase = preg_replace('/\?.*/', '', $uri);

        foreach ($this->endpoints as $search => $endpoint) {
            if (!Jasny\fnmatch_extended($search, $uriBase)) {
                continue;
            }

            $parts = explode('/', $uriBase);
            $url = preg_replace_callback('/\\$(\d+)/', function ($match) use ($parts) {
                $i = $match[1];
                return $parts[$i];
            }, $endpoint);

            break;
        }

        return $url;
    }

    /**
     * Check if URI has a URL
     *
     * @param string $uri
     * @return bool
     */
    public function hasUrl(string $uri): bool
    {
        return $this->findURL($uri) !== null;
    }

    /**
     * Get an URL from a URI
     *
     * @param string $uri
     * @return string
     * @throws OutOfRangeException if not URL exist for the URI
     */
    public function getUrl(string $uri): string
    {
        $url = $this->findURL($uri);

        if (!isset($url)) {
            throw new OutOfRangeException("Not URL found for '$uri'");
        }

        return $url;
    }


    /**
     * Check if URI has a URL for the 'done' request.
     *
     * @param string $uri
     * @return bool
     */
    public function hasDoneUrl(string $uri): bool
    {
        $doneUri = $this->getDoneUri($uri);

        return $this->hasUrl($doneUri);
    }

    /**
     * Get the URL for the 'done' request.
     *
     * @param string $uri
     * @return string
     */
    public function getDoneUrl(string $uri): string
    {
        $doneUri = $this->getDoneUri($uri);

        return $this->getUrl($doneUri);
    }

    /**
     * Get URI for done request based on resource URI.
     *
     * @param string $uri
     * @return string
     */
    protected function getDoneUri(string $uri): string
    {
        return preg_replace('/\?.*$/', '', $uri) . '/done';
    }
}
