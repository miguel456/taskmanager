<?php

namespace App\Core\Config;

/**
 * Classe rudimentar de configuração da aplicação. Carrega e devolve os ficheiros de configuração definidos.
 */
class Config
{

    private string $navbarConfig = '/config/navbar-menu.pt.json';

    private string $appConfig = '/config/app.ini';

    private array $loadedConfigs = [];

    /**
     * Carrega os ficheiros de configuração para a memória
     * @throws \JsonException
     * @throws \Exception
     */
    public function __construct()
    {
        $this->loadedConfigs[0] = parse_ini_file(realpath(__DIR__ . '/../../' . $this->appConfig), true);

        if (!$this->loadedConfigs[0]) {
            throw new \Exception('Configuração da aplicação inválida. Impossível continuar.');
        }
        $this->loadedConfigs[1] = json_decode(file_get_contents(realpath(__DIR__ . '/../../' . $this->navbarConfig)), true);
    }

    /**
     * Devolve o título correto da página para o caminho especificado conforme a configuração.
     * @param string $path O caminho da página atual
     * @return string O título a apresentar
     */
    public function resolveNavbarTitle(string $path): string {

        $title = $this->loadedConfigs[1]['title-associations']['default'];

        foreach ($this->loadedConfigs[1]['title-associations']['mappings'] as $keyword => $content) {
            if (str_contains($path, $keyword)) {
                $title = $content;
                break;
            }
        }
        return $title;
    }

    /**
     * Devolve as configurações da aplicação.
     * @return array
     */
    public function getAppConfig(): array {
        return $this->loadedConfigs[0]['App'];
    }

}