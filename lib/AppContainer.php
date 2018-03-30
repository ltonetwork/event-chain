<?php

use Mouf\Picotainer\Picotainer;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Jasny\HttpMessage\ServerRequest;
use Jasny\HttpMessage\Response;
use Jasny\RouterInterface;
use Jasny\Router\Routes;
use Jasny\Router\RoutesInterface;
use Jasny\ErrorHandler;
use Jasny\ErrorHandlerInterface;
use Jasny\ViewInterface;
use Symfony\Component\Yaml\Yaml;
use Monolog\Logger;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\PsrHandler;
use Rollbar\Rollbar;
use Assetic\FilterManager as AssetFilterManager;
use Assetic\AssetWriter;
use Assetic\Factory\AssetFactory;
use Assetic\Cache\FilesystemCache as AssetFilesystemCache;
use Jasny\Assetic\AssetCacheWorker;
use Jasny\Assetic\PersistentAssetWriter;
use LTO\AccountFactory;
use LTO\Account;

/**
 * The Application container.
 * 
 * @see https://github.com/thecodingmachine/picotainer
 * @codeCoverageIgnore
 */
class AppContainer extends Picotainer
{
    /**
     * Class constructor
     * 
     * @param array              $entries
     * @param ContainerInterface $delegateLookupContainer
     */
    public function __construct(array $entries = [], ContainerInterface $delegateLookupContainer = null)
    {
        $entries +=
            $this->getConfigEntry() +
            $this->getPSR7Entries() +
            $this->getRouterEntry() +
            $this->getLoggerEntry() +
            $this->getErrorHandlerEntry() +
            $this->getAsseticEntry() +
            $this->getViewEntry() +
            $this->getEmailFactoryEntry() +
            $this->getHttpClientEntry() +
            $this->getAccountFactoryEntry() +
            $this->getNodeEntry();
            
        parent::__construct($entries, $delegateLookupContainer);
    }

    /**
     * Get entry for app config
     * 
     * @return callback[]
     */
    protected function getConfigEntry()
    {
        return [
            'config' => function () {
                return new AppConfig(App::env());
            }
        ];
    }
    
    /**
     * Get the entries for the PSR-7 request and response
     * 
     * @return callback[]
     */
    protected function getPSR7Entries()
    {
        return [
            ServerRequestInterface::class => function () {
                return (new ServerRequest())->withGlobalEnvironment();
            },
            ResponseInterface::class => function () {
                return new Response();
            }
        ];
    }
    
    /**
     * Get router and routes entry
     * 
     * @return callback[]
     */
    protected function getRouterEntry()
    {
        return [
            RoutesInterface::class => function () {
                $setting = Yaml::parse(file_get_contents('config/routes.yml'));
                return new Routes\Glob($setting);
            },
            RouterInterface::class => function (ContainerInterface $container) {
                $routes = $container->get(RoutesInterface::class);
                return (new AppRouter($routes))->withMiddleware($container);
            },
            'route' => function (ContainerInterface $container) {
                return $container->get(RouterInterface::class); // Alias
            },
            'router' => function (ContainerInterface $container) {
                return $container->get(RouterInterface::class); // Alias
            }
        ];
    }

    /**
     * Get the logger entry
     * 
     * @return callback[]
     */
    protected function getLoggerEntry()
    {
        return [
            LoggerInterface::class => function (ContainerInterface $container) {
                $config = $container->get('config');
            
                if (Rollbar::logger() !== null) {
                    $logger = new Logger('', [new PsrHandler(Rollbar::logger())]);
                } elseif (!empty($config->log) && $config->log === 'browser') {
                    $logger = new Logger('', [new FirePHPHandler(), new ChromePHPHandler()]);
                } else {
                    $logger = new Logger('', [new ErrorLogHandler()]);
                }
                
                return $logger;
            },
            'logger' => function (ContainerInterface $container) {
                return $container->get(LoggerInterface::class); // Alias
            }
        ];
    }

    /**
     * Get the error handler entry
     * 
     * @return callback[]
     */
    protected function getErrorHandlerEntry()
    {
        return [
            ErrorHandlerInterface::class => function (ContainerInterface $container) {
                $errorHandler = new ErrorHandler();
                
                $logger = $container->get(LoggerInterface::class);
                if (isset($logger)) {
                    $errorHandler->setLogger($logger);
                }

                return $errorHandler;
            },
            'errorHandler' => function (ContainerInterface $container) {
                return $container->get(ErrorHandlerInterface::class); // Alias
            }
        ];
    }    
    
    /**
     * Get entry for managing assets
     * 
     * @return callback[]
     */
    protected function getAsseticEntry()
    {
        return [
            AssetFilterManager::class => function() {
                $fm = new Assetic\FilterManager();
                $fm->set('scss', new Assetic\Filter\ScssphpFilter());
                
                return $fm;
            },
            AssetFactory::class => function(ContainerInterface $container) {
                $factory = new AssetFactory('www', false); // `debug` to create non-combined files is broken in Assetic
                $factory->setFilterManager($container->get(AssetFilterManager::class));
                
                $cache = new AssetFilesystemCache('tmp/assets');
                $factory->addWorker(new AssetCacheWorker($cache));
                
                return $factory;
            },
            AssetWriter::class => function() {
                return new PersistentAssetWriter('www', [], !App::env('staging') && !App::env('prod'));
            }
        ];
    }
    
    /**
     * Get the entry for the view
     * 
     * @return callback[]
     */
    protected function getViewEntry()
    {
        return [
            ViewInterface::class => function (ContainerInterface $container) {
                $baseUrl = (string)$container->get(ServerRequestInterface::class)->getUri()->withUserInfo('')
                    ->withPath('/')->withQuery('')->withFragment('')->withPort('');
                
                $view = new Jasny\View\Twig([
                    'path' => ['views', 'email' => 'views/email'],
                    'cache' => 'tmp/views',
                    'auto_reload' => !App::env('staging') && !App::env('prod')
                ]);
                
                $config = $container->get('config');

                $view->addPlugin(new Jasny\View\Plugin\DefaultTwigExtensions());
                $view->addPlugin(new Jasny\View\Plugin\TwigAssetic($container->get(AssetFactory::class),
                    $container->get(AssetWriter::class)));
                
                $view->getTwig()->addGlobal('base_url', $baseUrl);
                $view->getTwig()->addGlobal('auth', $container->get('auth'));
                $view->getTwig()->addGlobal('tracking', isset($config->tracking) ? $config->tracking : null);
                $view->getTwig()->addGlobal('app', $config->app);

                $view->getTwig()->addFilter(new Twig_SimpleFilter('markdown', function($text) {
                    return (new Parsedown())->text($text);
                }));

                $view->getTwig()->addFilter(new Twig_SimpleFilter('valid_url', function($url) {
                    if (empty($url)) {
                        return '';
                    }

                    $addScheme = !preg_match('|^https?://|i', $url) && !preg_match('|^/[^/]+|', $url);
                    return $addScheme ? 'http://' . $url : $url;
                }));

                $view->getTwig()->addFilter(new Twig_SimpleFilter('period', 'date_describe_period'));
                $view->getTwig()->addFilter(new Twig_SimpleFilter('period_until', 'date_describe_period_until'));
                
                return $view;
            },
            'view' => function (ContainerInterface $container) {
                return $container->get(ViewInterface::class); // Alias
            }
        ];
    }
    
    /**
     * Get the email factory entry
     * 
     * @return callback[]
     */
    protected function getEmailFactoryEntry()
    {
        return [
            'email' => function (ContainerInterface $container) {
                $twig = $container->get('view')->getTwig();
                $options = arrayify($container->get('config')->email) + ['templateNs' => 'email'];

                return new EmailFactory($twig, $options);
            }
        ];
    }
    
    /**
     * Get the Guzzle HTTP client entry
     * 
     * @return callback[]
     */
    protected function getHttpClientEntry()
    {
        return [
            GuzzleHttp\ClientInterface::class => function() {
                return $client = new GuzzleHttp\Client(['timeout' => 2]);
            },
            'httpClient' => function (ContainerInterface $container) {
                return $container->get(GuzzleHttp\ClientInterface::class); // Alias
            }
        ];
    }
    
    /**
     * Get account factory entry
     * 
     * @return callback[]
     */
    protected function getAccountFactoryEntry()
    {
        return [
            AccountFactory::class => function(ContainerInterface $container) {
                $config = $container->get('config');
                
                return new AccountFactory(isset($config->network) ? $config->network : 'T');
            }
        ];
    }
    
    /**
     * Get node account entry
     * 
     * @return callback[]
     */
    protected function getNodeEntry()
    {
        return [
            'node' => function(ContainerInterface $container) {
                $factory = $container->get(AccountFactory::class);
                $data = Jasny\arrayify(new Jasny\Config('config/node.yml'));
                
                return $factory->create($data);
            }
        ];
    }
}

