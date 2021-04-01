<?php


namespace Payum\Bundle\PayumBundle\Twig;


use Payum\Core\Bridge\Spl\ArrayObject;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;

class PathRegistrar
{
    /**
     * @var FilesystemLoader
     */
    protected $fileLoader;

    public function __construct($twig)
    {
        $this->fileLoader = new FilesystemLoader();

        $currentLoader = $twig->getLoader();
        if ($currentLoader instanceof ChainLoader) {
            $currentLoader->addLoader($this->fileLoader);
        } else {
            $twig->setLoader(new ChainLoader([$currentLoader, $this->fileLoader]));
        }
    }

    public function register(ArrayObject $config)
    {
        $paths = $config['payum.paths'];

        foreach ($paths as $namespace => $path) {
            $this->fileLoader->addPath($path, $namespace);
        }
    }

    public function __invoke()
    {
        return call_user_func_array([$this, 'register'], func_get_args());
    }
}
