<?php
App::uses('AppController','Controller');

class HeadlinesController extends AppController {

    public function test() {
        // test if controller is working
        $this->set('message', 'Controller is working!');
        $this->render('test');
    }

    // save settings to config.php
    public function saveSettings() {
        $this->response->type('json');
        $this->autoRender = false;

        if ($this->request->is('post')) {
            try {
                Configure::write('NewsSettings.query', $this->request->data['query']);
                Configure::write('NewsSettings.language', $this->request->data['language']);
                Configure::write('NewsSettings.pageSize', $this->request->data['pageSize']);
                Configure::write('NewsSettings.sortBy', $this->request->data['sortBy']);
                Configure::write('NewsSettings.includeDomains', $this->request->data['includeDomains']);
                Configure::write('NewsSettings.excludeDomains', $this->request->data['excludeDomains']);

                // success response
                $res = array(
                    'status' => 'success',
                    'message' => 'Settings saved successfully.'
                );
                $this->response->body(json_encode($res));

            } catch (Exception $e) {
                // error response
                $res = array(
                    'status' => 'error',
                    'message' => 'Failed to save settings: ' . $e->getMessage()
                );
                $this->response->body(json_encode($res));

            }
        } else {
            // invalid request method
            $res = array(
                'status' => 'error',
                'message' => 'Invalid request method.'
            );
            $this->response->body(json_encode($res));

        }
    }
}