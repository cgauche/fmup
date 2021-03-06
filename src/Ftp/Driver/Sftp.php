<?php
namespace FMUP\Ftp\Driver;

use FMUP\Ftp\FtpAbstract;
use FMUP\Ftp\Exception as FtpException;
use FMUP\Logger;

class Sftp extends FtpAbstract implements Logger\LoggerInterface
{
    use Logger\LoggerTrait;

    const METHODS = 'methods';
    const CALLBACKS = 'callbacks';
    const USE_INCLUDE_PATH = 'use_include_path';
    const GET_CONTENT_CONTEXT = 'get_content_context';
    const PUT_CONTENT_CONTEXT = 'put_content_context';
    const PUT_CONTENT_FLAGS = 'put_content_flags';
    const OFFSET = 'offset';
    const MAXLEN = 'maxlen';
    const CREATE_MODE = 'create_mode';

    private $sftpSession;

    /**
     * @return resource
     * @throws FtpException
     */
    protected function getSftpSession()
    {
        if (!$this->sftpSession) {
            $this->sftpSession = $this->ssh2Sftp($this->getSession());
        }
        return $this->sftpSession;
    }

    /**
     * @return array|null
     */
    protected function getMethods()
    {
        return isset($this->settings[self::METHODS]) ? $this->settings[self::METHODS] : null;
    }

    /**
     * @return array|null
     */
    protected function getCallbacks()
    {
        return isset($this->settings[self::CALLBACKS]) ? $this->settings[self::CALLBACKS] : null;
    }

    /**
     * Optional param for file_get_content
     * @return bool
     */
    protected function getUseIncludePath()
    {
        return isset($this->settings[self::USE_INCLUDE_PATH]) ? $this->settings[self::USE_INCLUDE_PATH] : false;
    }

    /**
     * Optional param for file_get_content
     * @return resource|null
     */
    protected function getGetContentContext()
    {
        return isset($this->settings[self::GET_CONTENT_CONTEXT]) ? $this->settings[self::GET_CONTENT_CONTEXT] : null;
    }

    /**
     * Optional param for file_get_content
     * @return int
     */
    protected function getOffset()
    {
        return isset($this->settings[self::OFFSET]) ? $this->settings[self::OFFSET] : 0;
    }

    /**
     * Optional param for file_get_content
     * @return int|null
     */
    protected function getMaxLen()
    {
        return isset($this->settings[self::MAXLEN]) ? $this->settings[self::MAXLEN] : null;
    }

    /**
     * Optional param for file_put_content
     * @return int
     */
    protected function getPutContentFlags()
    {
        return isset($this->settings[self::PUT_CONTENT_FLAGS]) ? $this->settings[self::PUT_CONTENT_FLAGS] : 0;
    }

    /**
     * Optional param for file_put_content
     * @return resource|null
     */
    protected function getPutContentContext()
    {
        return isset($this->settings[self::PUT_CONTENT_CONTEXT]) ? $this->settings[self::PUT_CONTENT_CONTEXT] : null;
    }

    /**
     * Optional param for file_get_content
     * @return int
     */
    protected function getCreateMode()
    {
        return isset($this->settings[self::CREATE_MODE]) ? $this->settings[self::CREATE_MODE] : 0644;
    }

    /**
     * @param string $host
     * @param int $port
     * @return $this
     * @throws FtpException
     */
    public function connect($host, $port = 22)
    {

        $this->setSession($this->ssh2Connect($host, $port, $this->getMethods(), $this->getCallbacks()));
        return $this;
    }

    /**
     * @param string $host
     * @param int $port
     * @param array|null $methods
     * @param array|null $callbacks
     * @return resource
     * @codeCoverageIgnore
     */
    protected function ssh2Connect($host, $port = 22, array $methods = null, array $callbacks = null)
    {
        return ssh2_connect($host, $port, $methods, $callbacks);
    }

    /**
     * @param string $user
     * @param string $pass
     * @return bool
     * @throws FtpException
     */
    public function login($user, $pass)
    {
        $ret = $this->ssh2AuthPassword($this->getSession(), $user, $pass);
        if (!$ret) {
            $this->log(Logger::ERROR, "Unable to login to SFTP server", (array)$this->getSettings());
            throw new FtpException('Unable to login to the SFTP server');
        }
        return $ret;
    }

    /**
     * @param resource $session
     * @param string $username
     * @param string $password
     * @return bool
     * @codeCoverageIgnore
     */
    protected function ssh2AuthPassword($session, $username, $password)
    {
        return ssh2_auth_password($session, $username, $password);
    }

    /**
     * @param string $localFile
     * @param string $remoteFile
     * @return int
     */
    public function get($localFile, $remoteFile)
    {
        if ($this->getMaxLen() === null) {
            $fileContent = $this->fileGetContents(
                'ssh2.sftp://' . $this->getSftpSession() . '/' . $remoteFile,
                $this->getUseIncludePath(),
                $this->getGetContentContext(),
                $this->getOffset()
            );
        } else {
            $fileContent = $this->fileGetContents(
                'ssh2.sftp://' . $this->getSftpSession() . '/' . $remoteFile,
                $this->getUseIncludePath(),
                $this->getGetContentContext(),
                $this->getOffset(),
                $this->getMaxLen()
            );
        }
        return $this->filePutContents(
            $localFile,
            $fileContent,
            $this->getPutContentFlags(),
            $this->getPutContentContext()
        );
    }

    /**
     * Put file on ftp server
     * @param string $remoteFile
     * @param string $localFile
     * @return bool
     */
    public function put($remoteFile, $localFile)
    {
        if ($this->getMaxLen() === null) {
            $fileContent = $this->fileGetContents(
                $localFile,
                $this->getUseIncludePath(),
                $this->getGetContentContext(),
                $this->getOffset()
            );
        } else {
            $fileContent = $this->fileGetContents(
                $localFile,
                $this->getUseIncludePath(),
                $this->getGetContentContext(),
                $this->getOffset(),
                $this->getMaxLen()
            );
        }
        return $this->filePutContents(
            'ssh2.sftp://' . intval($this->getSftpSession()) . '/' . $remoteFile,
            $fileContent,
            $this->getPutContentFlags(),
            $this->getPutContentContext()
        );
    }

    /**
     * @param resource $session
     * @param string $localFile
     * @param string $remoteFile
     * @param int $createMode
     * @return bool
     * @codeCoverageIgnore
     */
    protected function ssh2ScpSend($session, $localFile, $remoteFile, $createMode)
    {
        return ssh2_scp_send($session, $localFile, $remoteFile, $createMode);
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    protected function fileGetContents()
    {
        return call_user_func_array('file_get_contents', func_get_args());
    }

    /**
     * @param string $fileName
     * @param mixed $data
     * @param int $flags
     * @param resource $context
     * @return int
     * @codeCoverageIgnore
     */
    protected function filePutContents($fileName, $data, $flags = 0, $context = null)
    {
        return file_put_contents($fileName, $data, $flags, $context);
    }

    /**
     * @param string $file
     * @return bool
     */
    public function delete($file)
    {
        return $this->ssh2SftpUnlink($this->getSftpSession(), $file);
    }

    /**
     * @param resource $sftpSession
     * @param string $path
     * @return bool
     * @codeCoverageIgnore
     */
    protected function ssh2SftpUnlink($sftpSession, $path)
    {
        return ssh2_sftp_unlink($sftpSession, $path);
    }

    /**
     *
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * @param resource $session
     * @return resource
     * @codeCoverageIgnore
     */
    protected function ssh2Sftp($session)
    {
        return ssh2_sftp($session);
    }
}
