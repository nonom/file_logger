<?php

namespace Drupal\file_logger\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\State\State;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure File Logger service.
 *
 * @package Drupal\file_logger\Form
 */
class FileLoggerConfigurationForm extends FormBase {

  /**
   * The state.
   *
   * @var \Drupal\Core\State\State
   */
  protected $state;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Configuration constructor.
   *
   * @param \Drupal\Core\State\State $state
   *   The state.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(State $state, MessengerInterface $messenger) {
    $this->state = $state;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
      $container->get('state'),
      $container->get('messenger'),
    );
  }

  /**
   * Getter method for Form ID.
   *
   * The form ID is used in implementations of hook_form_alter() to allow other
   * modules to alter the render array built by this form controller. It must be
   * unique site wide. It normally starts with the providing module's name.
   *
   * @return string
   *   The unique ID of the form defined by this class.
   */
  public function getFormId(): string {
    return 'file_logger_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['file_logger'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('File logger'),
      '#description' => $this->t('File logger configuration.'),
    ];
    $form['file_logger']['file_logger_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable file logger in CSV.'),
      '#default_value' => $this->state->get('file_logger_enabled'),
      '#required' => FALSE,
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // @todo Adding more configuration variants. Monolog, Graylog, Watchdog, etc.
    $values = [
      'file_logger_enabled',
    ];
    foreach ($values as $value) {
      $this->state->set($value, $form_state->getValue($value));
    }
  }

}
