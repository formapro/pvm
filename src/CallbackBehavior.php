<?php
namespace Formapro\Pvm;

class CallbackBehavior implements Behavior, SignalBehavior
{
    /**
     * @var \Closure
     */
    private $execute;

    /**
     * @var \Closure
     */
    private $signal;

    /**
     * @param \Closure $execute
     * @param \Closure $signal
     */
    public function __construct(\Closure $execute, \Closure $signal = null)
    {
        $this->execute = $execute;
        $this->signal = $signal;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Token $token)
    {
        return call_user_func($this->execute, $token);
    }

    /**
     * {@inheritdoc}
     */
    public function signal(Token $token)
    {
        return $this->execute($token);

        if ($this->signal) {
            return call_user_func($this->signal, $token);
        }
    }
}
