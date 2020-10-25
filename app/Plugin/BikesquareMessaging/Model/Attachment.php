<?php

class Attachment extends BikesquareMessagingAppModel {	
    
    public $actsAs = array('Containable');
    
    public $useTable = 'messaging_attachments';

}
