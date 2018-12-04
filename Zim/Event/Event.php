<?php
/**
 * File Event.php
 * @henter
 * Time: 2018-11-24 19:56
 *
 */

namespace Zim\Event;

class Event
{
    //uncaught exception
    const EXCEPTION = 'zim.exception';

    //before route match
    const ROUTE = 'zim.route';

    //before request dispatch
    //TODO, replaced with RequestEvent
    const REQUEST = 'zim.request';

    //controller found
    const CONTROLLER = 'zim.controller';

    //action found
    const ACTION = 'zim.action';

    //response created
    const RESPONSE = 'zim.response';

    //response sent
    const TERMINATE = 'zim.terminate';

}