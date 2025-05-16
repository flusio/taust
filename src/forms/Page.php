<?php

namespace taust\forms;

use Minz\Form;
use Minz\Validable;
use taust\models;

/**
 * @extends BaseForm<models\Page>
 */
class Page extends BaseForm
{
    /** @var string[] */
    #[Form\Field(bind: false)]
    public array $domain_ids = [];

    /** @var string[] */
    #[Form\Field(bind: false)]
    public array $server_ids = [];

    #[Form\Field(transform: 'trim')]
    public string $hostname = '';

    #[Form\Field]
    public string $style = '';

    #[Form\Field]
    public string $locale = '';

    public function servers(): array
    {
        return models\Server::listAllOrderById();
    }

    public function isServerSelected(models\Server $server): bool
    {
        return in_array($server->id, $this->server_ids);
    }

    public function domains(): array
    {
        return models\Domain::listAllOrderById();
    }

    public function isDomainSelected(models\Domain $domain): bool
    {
        return in_array($domain->id, $this->domain_ids);
    }

    #[Validable\Check]
    public function checkUniqueHostname(): void
    {
        if ($this->hostname === '') {
            return;
        }

        $current_page = $this->model();
        $existing_page = models\Page::findBy([
            'hostname' => $this->hostname,
        ]);

        if ($existing_page && $existing_page->id !== $current_page->id) {
            $this->addError('hostname', 'unique_hostname', _('A page already has the same hostname.'));
        }
    }
}
