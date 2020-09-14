<?php
namespace FMUP;

/**
 * Class Mail - PHPMailer decorator
 * @package FMUP
 * @todo implement a real mail manager system
 */
class Mail extends \PHPMailer\PHPMailer\PHPMailer
{
    private $config;

    /**
     * @param Config\ConfigInterface $config
     * @param bool|false $exceptions
     */
    public function __construct(\FMUP\Config\ConfigInterface $config, $exceptions = false)
    {
        parent::__construct($exceptions);
        $this->config = $config;

        if ($config->get('smtp_serveur') != 'localhost') {
            $this->IsSMTP();
        }
        $this->IsHTML(true);
        $this->CharSet = "UTF-8";
        $this->SMTPAuth = $config->get('smtp_authentification');
        $this->SMTPSecure = $config->get('smtp_secure');
        $this->SMTPAutoTLS = $config->get('smtp_secure') == 'tls';

        $this->Host = $config->get('smtp_serveur');
        $this->Port = $config->get('smtp_port');

        if ($config->get('smtp_authentification')) {
            $this->Username = $config->get('smtp_username'); // Gmail identifiant
            $this->Password = $config->get('smtp_password'); // Gmail mot de passe
        }
    }

    /**
     * Remplace tokens in a string
     * @param string $message Text to parse
     * @param array $tokens Associative array for tokens to replace
     * @return string treated message
     * @uses self::tokenReplaceMap
     */
    public static function replaceTokens($message = '', array $tokens = array())
    {
        $search = array_keys($tokens);
        $replace = array_values($tokens);
        $search = array_map(array(__CLASS__, 'tokenReplaceMap'), $search);
        return str_replace($search, $replace, $message);
    }

    /**
     * Replace map
     * @param string $o
     * @return string
     * @usedby self::replaceTokens
     * @SuppressWarnings(PMD.UnusedPrivateMethod)
     */
    private static function tokenReplaceMap($o)
    {
        return "{" . $o . "}";
    }
}
