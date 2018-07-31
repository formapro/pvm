# PHP Process virtual machine

The library provides tooling to build/execute/visualise [workflows](https://en.wikipedia.org/wiki/Workflow), [BPMN](http://www.bpmn.org/) like processes, or [state machines](https://en.wikipedia.org/wiki/Finite-state_machine).

It works like this: you build a process, create nodes and transitions, link them up. Than you can do:

* Save process and execute later.
* Clone and execute several processes.
* Pause & continue execution. 
* Supports async transition. Execute tasks in parallel.
* Supports fork-join, conditions, cycles 

It is backed up by [workflow nets (WF-nets)](https://en.wikipedia.org/wiki/Petri_net) and [graphs](https://en.wikipedia.org/wiki/Graph_theory) theories. Here's a good [post on how Petri Nets works](https://www.techfak.uni-bielefeld.de/~mchen/BioPNML/Intro/pnfaq.html)

![Example](docs/images/pizza-process.png)
        
## Examples

Checkout [PVM Examples](https://pvm-demo.forma-pro.com/) site.

* [Sequence](https://pvm-demo.forma-pro.com/demo/sequence)
* [Fork](https://pvm-demo.forma-pro.com/demo/fork)
* [Conditions](https://pvm-demo.forma-pro.com/demo/condition)
* [Cycles](https://pvm-demo.forma-pro.com/demo/cycle)
* [Synchronization](https://pvm-demo.forma-pro.com/demo/synchronization)
* [Pause and continue](https://pvm-demo.forma-pro.com/demo/pause-and-contiue)
* [Clone and Modify](https://pvm-demo.forma-pro.com/demo/clone-and-modify)
* [Grouping](https://pvm-demo.forma-pro.com/demo/grouping)
* [Exception](https://pvm-demo.forma-pro.com/demo/demo/exception)
* [Store to File](https://pvm-demo.forma-pro.com/demo/store-to-file)
* [Store to MongoDB](https://pvm-demo.forma-pro.com/demo/store-to-mongodb)
* [Store with Doctrine](http://pvm-demo.forma-pro.com/demo/store-to-mysql)
* [Parallel execution](https://pvm-demo.forma-pro.com/demo/parallel-execution)
* [Delayed transtions](docs/delayed-execution-with-quartz.md) (require [php-quartz/quartz](https://github.com/php-quartz/quartz))
* [Gallery](docs/gallery.md)
* [All examples](https://pvm-demo.forma-pro.com/)

## Developed by Forma-Pro

Forma-Pro is a full stack development company which interests also spread to open source development. 
Being a team of strong professionals we have an aim an ability to help community by developing cutting edge solutions in the areas of e-commerce, docker & microservice oriented architecture where we have accumulated a huge many-years experience. 
Our main specialization is Symfony framework based solution, but we are always looking to the technologies that allow us to do our job the best way. We are committed to creating solutions that revolutionize the way how things are developed in aspects of architecture & scalability.

If you have any questions and inquires about our open source development, this product particularly or any other matter feel free to contact at opensource@forma-pro.com

## License 

MIT
