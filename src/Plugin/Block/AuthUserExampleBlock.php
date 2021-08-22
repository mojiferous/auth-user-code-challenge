<?php

namespace Drupal\auth_user_example\Plugin\Block;

use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an authenticated user information block.
 *
 * @Block(
 *   id = "auth_user_example_block",
 *   admin_label = @Translation("Authenticated User Information Block"),
 *   category = @Translation("Code Challenge Blocks")
 * )
 */
class AuthUserExampleBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current Drupal user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * {@inheritDoc}
   */
  public function build() {

    $build['content'] = [
      '#theme' => 'auth_user_example_block',
      '#username' => '',
      '#last_login' => '',
      '#profile_link' => '',
      '#admin_message' => '',
    ];

    if ($this->currentUser->isAuthenticated()) {
      $build['content']['#username'] = $this->currentUser->getDisplayName();

      // Build the last login formatted time.
      $last_login_time = $this->currentUser->getLastAccessedTime();
      $build['content']['#last_login'] = date('F jS, Y h:i a', $last_login_time);

      // Build the user profile link.
      $user_profile_url = Url::fromRoute('user.page');
      $build['content']['#profile_link'] = Link::fromTextAndUrl('Visit your profile', $user_profile_url);
    }

    $hide_anonymous_message = \Drupal::config('auth_user_example.settings')->get('hide_message_for_anonymous');
    if (($this->currentUser->isAnonymous() && !$hide_anonymous_message) || $this->currentUser->isAuthenticated()) {
      $build['content']['#admin_message'] = \Drupal::config('auth_user_example.settings')->get('user_message');
    }

    return $build;
  }

  /**
   * {@inheritDoc}
   */
  public function blockAccess(AccountInterface $account) {
    $hide_anonymous_message = \Drupal::config('auth_user_example.settings')->get('hide_message_for_anonymous');

    // Only show this block to logged-in users if hide message is enabled.
    if ($hide_anonymous_message) {
      return AccessResultAllowed::allowedIf($account->isAuthenticated());
    }

    return AccessResultAllowed::allowed();
  }

  /**
   * AuthUserExampleBlock constructor.
   *
   * @param array $configuration
   *   Plugin config.
   * @param string $plugin_id
   *   Plugin ID.
   * @param array $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Current drupal user.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, AccountInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user'),
    );
  }

  /**
   * {@inheritDoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['user']);
  }

}
