<?php

namespace undertext\idrupalextension;

use Behat\MinkExtension\ServiceContainer\MinkExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Isolated Drupal Extension.
 */
class IsolatedDrupalExtension implements Extension {

  /**
   * {@inheritDoc}
   */
  public function getConfigKey() {
    return "idrupalextension";
  }

  /**
   * {@inheritDoc}
   */
  public function initialize(ExtensionManager $extensionManager) {

  }

  /**
   * {@inheritDoc}
   *
   * There are only 2 config options for this extension:
   *  - profile: name of the installation profile for test site installation
   *  - reuse_installation: if set to TRUE then test site will be created only
   *    once, will be reused and not deleted at the end of a test process.
   */
  public function configure(ArrayNodeDefinition $builder) {
    $builder
      ->addDefaultsIfNotSet()
      ->children()
      ->scalarNode('profile')
      ->defaultValue('standard')
      ->info('The name of profile to install')
      ->end()
      ->scalarNode('reuse_installation')
      ->defaultValue(FALSE)
      ->info('Reuse installed test site')
      ->end()
      ->end()
      ->end();
  }

  /**
   * {@inheritDoc}
   */
  public function load(ContainerBuilder $container, array $config) {
    $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../config'));
    $loader->load('services.yml');
    $container->setParameter('idrupalextension.profile', $config['profile']);
    $container->setParameter('idrupalextension.reuse_installation', $config['reuse_installation']);

    $this->replaceMinkService($container);
  }

  /**
   * Replace Mink service provided by Behat with our custom service which
   * takes care of cookies management.
   *
   * @param ContainerBuilder $container
   *   DI container builder.
   */
  protected function replaceMinkService($container) {
    $minkDefinition = $container->getDefinition(MinkExtension::MINK_ID);
    $minkDefinition->setClass('\undertext\idrupalextension\DrupalAwareMink');
    $minkDefinition->setArguments([
      new Reference('idrupalextension.test_site_installer'),
      '%mink.base_url%',
    ]);
    $container->setDefinition(MinkExtension::MINK_ID, $minkDefinition);
  }

  /**
   * {@inheritDoc}
   */
  public function process(ContainerBuilder $container) {

  }

}
