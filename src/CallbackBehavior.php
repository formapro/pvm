<?php
namespace Formapro\Pvm;

class CallbackBehavior implements Behavior
{
    /**
     * @var \Closure
     */
    private $callback;

    /**
     * @param \Closure $callback
     */
    public function __construct(\Closure $callback)
    {
        $this->callback = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Token $token)
    {
        return call_user_func($this->callback, $token);
    }
}
