<?php
namespace Formapro\Pvm;

use function Formapro\Values\get_values;

class FileDAL extends InMemoryDAL
{
    /**
     * @var string
     */
    private $storageDir;

    public function __construct(string $storageDir)
    {
        $this->storageDir = $storageDir;
    }

    public function persistToken(Token $token): void
    {
        $this->persistProcess($token->getProcess());

        $tokenFile = $this->storageDir.'/'.$token->getId().'.json';
        if (false == file_exists($tokenFile)) {
            symlink($this->getProcessFile($token->getProcess()->getId()), $tokenFile);
        }
    }

    public function persistProcess(Process $process): void
    {
        file_put_contents($this->getProcessFile($process->getId()), json_encode(get_values($process)));
    }

    public function getToken(string $id): Token
    {
        $tokenFile = $this->storageDir.'/'.$id.'.json';
        if (false == file_exists($tokenFile)) {
            throw new \InvalidArgumentException('Token could not be found. Id: '.$id);
        }

        $json = file_get_contents($tokenFile);
        $values = json_decode($json, true);
        if (false == is_array($values)) {
            throw new \LogicException('Invalid process file. File: '.$tokenFile);
        }

        $process = Process::create($values);

        return $this->getProcessToken($process, $id);
    }

    private function getProcessFile(string $processId): string
    {
        return $this->storageDir.'/'.$processId.'.json';
    }
}
