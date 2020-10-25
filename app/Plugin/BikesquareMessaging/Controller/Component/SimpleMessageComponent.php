<?php 

App::uses('Component', 'Controller');
App::uses('HttpSocket', 'Network/Http');

class SimpleMessageComponent extends Component {

  public function inviaMessaggio($data, $to_ids, $user, $attivita_id, $conversation_id=null)
  {
    $UserModel = ClassRegistry::init('User');
    
    //Se l'utente è loggato mi serve aggiungere la sua pwd in modo da poterla passare al messaging
    $user['password'] = $UserModel->getHashedPwd($user['id']);
    
      if (empty($conversation_id))
      {
        //Invece del redirect faccio un POST versio Messaging.Conversation.edit
        if (isset($data['cognome']))
        {
            $nomeUtente = $data['cognome'] . ' ' . $data['nome'];
        }
        else
        {
            $nomeUtente = $user['username'];
        }
        $messageData = [
            'Conversation' => [
                'author_id' =>  $user['id'],
                'subject' => __('Messaggio dal Web') .' da ' . $nomeUtente,     
                'link_id' => '', // devo specificarlo in ogni caso 
                'attivita_id' => $attivita_id              
            ],
            'Participant' => [
                'Participant' => $to_ids
            ], 
            'Tag' => [
                'Tag' => ['nuovo'] // anche se non esiste lo devo mettere (se non vuoto non lo passa)
            ]                
        ];
      }
      else
      {
        $messageData = [
                            'Conversation'=>[
                                'subject' => $data['subject'],
                                'link_id' => '', // devo specificarlo in ogni caso 
                            ],
                       ];
      }
      $messageData['Message'][0]['content'] = $data['messaggio'];      
      $HttpSocket = new HttpSocket();
      $url = Router::url([
          'plugin' => 'messaging',
          'controller' => 'conversations',
          'action' => ($conversation_id ? "edit/$conversation_id.json": "edit.json" ),
          '?' => [
              'authkey' => $user['username'],
              'authpass' => $user['password'],
          ]
      ], true);

      $result = $HttpSocket->post($url, $messageData);
      
      if( $result->code == 200 && $result->body && json_decode($result->body)->success == true ) {
          return true;
      }
      return false;
  }      
} 

