<?php

/*
 * This file is part of the KnpRadBundle package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\Bundle\RadBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Finder\Finder;
use Symfony\Bundle\FrameworkBundle\Command\AssetsInstallCommand as BaseCommand;

use Knp\Bundle\RadBundle\Bundle\ApplicationBundle;

/**
 * Redefines Symfony2 install command to support application bundles.
 */
class AssetsInstallCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addOption('symlink', null, InputOption::VALUE_NONE, 'Symlinks the assets instead of copying it')
            ->addOption('relative', null, InputOption::VALUE_NONE, 'Make relative symlinks')
            ->setDescription('Installs bundles web assets under a public web directory')
            ->setHelp(<<<EOT
The <info>assets:install</info> command installs bundle assets into a web
directory.

<info>php app/console assets:install</info>

A "bundles" directory will be created inside the target directory, and the
"Resources/public" directory of each bundle will be copied into it.

To create a symlink to each bundle instead of copying its assets, use the
<info>--symlink</info> option:

<info>php app/console assets:install --symlink</info>

To make symlink relative, add the <info>--relative</info> option:

<info>php app/console assets:install --symlink --relative</info>

EOT
            )
            ->setName('assets:install')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $targetArg = rtrim($this->getContainer()->getParameter('kernel.web_dir'), '/');

        if (!is_dir($targetArg)) {
            throw new \InvalidArgumentException(sprintf('The target directory "%s" does not exist.', $input->getArgument('target')));
        }

        if (!function_exists('symlink') && $input->getOption('symlink')) {
            throw new \InvalidArgumentException('The symlink() function is not available on your system. You need to install the assets without the --symlink option.');
        }

        $filesystem = $this->getContainer()->get('filesystem');

        // Create the bundles directory otherwise symlink will fail.
        $filesystem->mkdir($targetArg.'/public/', 0777);

        foreach ($this->getContainer()->get('kernel')->getBundles() as $bundle) {
            $originDir = $bundle->getPath().'/Resources/public';
            if ($bundle instanceof ApplicationBundle) {
                $originDir = $bundle->getPath().'/public';
            }

            if (is_dir($originDir)) {
                $targetDir = $targetArg.'/public/'.preg_replace('/bundle$/', '', strtolower($bundle->getName()));

                if ($bundle instanceof ApplicationBundle) {
                    $targetDir = $targetArg.'/public/app';
                }

                $output->writeln(sprintf('Installing assets for <comment>%s</comment> into <comment>%s</comment>', $bundle->getNamespace(), $targetDir));

                $filesystem->remove($targetDir);

                if ($input->getOption('symlink')) {
                    $filesystem->symlink($originDir, $targetDir);
                } else {
                    $filesystem->mkdir($targetDir, 0777);
                    // We use a custom iterator to ignore VCS files
                    $filesystem->mirror($originDir, $targetDir, Finder::create()->in($originDir));
                }
            }
        }
    }
}
