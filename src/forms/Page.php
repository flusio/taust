<?php

namespace taust\forms;

use Minz\Form;
use Minz\Validable;
use taust\models;

/**
 * @extends BaseForm<models\Page>
 *
 * @author Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
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

    /**
     * @param array<string, mixed> $default_values
     */
    public function __construct(array $default_values = [], ?models\Page $model = null)
    {
        if ($model) {
            $default_values['domain_ids'] = array_column($model->domains(), 'id');
            $default_values['server_ids'] = array_column($model->servers(), 'id');
        }

        parent::__construct($default_values, $model);
    }

    /**
     * @return models\Server[]
     */
    public function servers(): array
    {
        return models\Server::listAllOrderById();
    }

    public function isServerSelected(models\Server $server): bool
    {
        return in_array($server->id, $this->server_ids);
    }

    /**
     * @return models\Domain[]
     */
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
