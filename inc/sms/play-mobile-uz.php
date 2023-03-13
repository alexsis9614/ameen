<?php
    /**
     * This is just an example.
     *
     *
     *
     *
     * $play_mobile = new STM_LMS_Play_Mobile('username', 'password');
     *
     * $play_mobile->create([
     *      'baseUrl' => "http://91.204.239.44/broker-api/"
     * ]);
     *
     * $response = $play_mobile->send([
     *  [
     *      'recipient' => "998936913932",
     *      'message-id' => "1",
     *      'originator' => "3700",
     *      'text' => "test",
     *  ],[
     *      'recipient' => "998936913932",
     *      'message-id' => "1",
     *      'originator' => "3700",
     *      'text' => "test",
     *  ],
     * ]);
     * echo $response == false ? "Not sent" : "Send successfully";
     */
    class STM_LMS_Play_Mobile
    {
        /**
         * @var string $baseUrl
         */
        public $baseUrl = "http://91.204.239.44/broker-api/";

        /**
         * @var string $username
         */
        private $username;

        /**
         * @var string $password
         */
        private $password;

        /**
         * @var $args
         */
        private $args;

        /**
         * STM_LMS_Play_Mobile constructor.
         * @param $username
         * @param $password
         */
        public function __construct($username, $password)
        {
            $this->setUsername( $username );
            $this->setPassword( $password );
        }

        /**
         * @return string
         */
        public function getBaseUrl(): string
        {
            return $this->baseUrl;
        }

        /**
         * @param string $baseUrl
         */
        public function setBaseUrl(string $baseUrl): void
        {
            $this->baseUrl = $baseUrl;
        }

        /**
         * @return string
         */
        public function getUsername(): string
        {
            return $this->username;
        }

        /**
         * @param string $username
         */
        public function setUsername(string $username): void
        {
            $this->username = $username;
        }

        /**
         * @return string
         */
        public function getPassword(): string
        {
            return $this->password;
        }

        /**
         * @param string $password
         */
        public function setPassword(string $password): void
        {
            $this->password = $password;
        }

        /**
         * @param array $options
         */
        public function create(array $options = [])
        {
            if (isset($options['baseUrl'])) {
                $this->setBaseUrl( $options['baseUrl'] );
            }
            $this->args = array(
                'headers' => ['Authorization' => "Basic " . base64_encode($this->getUsername() . ":" . $this->getPassword())]
            );
        }

        /**
         * @param array $messages
         * @return false|string
         * @throws Exception
         */
        public function send(array $messages = [])
        {
            $data = [];
            foreach ($messages as $message) {
                if (isset($message["recipient"]) && isset($message["message-id"]) && isset($message["originator"]) && isset($message["text"])) {
                    $data[] = [
                        "recipient" => $message["recipient"],
                        "message-id" => $message["message-id"],
                        "sms" => [
                            "originator" => $message["originator"],
                            "content" => [
                                "text" => $message['text']
                            ]
                        ]
                    ];
                }
            }

            $args             = $this->args;
            $args['messages'] = $data;
            $request          = wp_remote_post( $this->getBaseUrl(), $args );

            if ( is_wp_error( $request ) ) {
                wp_send_json(
                    array(
                        'message' => $request->get_error_message(),
                        'status'  => 'error'
                    )
                );
            } else if ( wp_remote_retrieve_response_code($request) !== 200 ) {
                $error_message = '';
                $response      = json_decode( wp_remote_retrieve_body($request), true );

                if ( isset( $response['error'] ) ) {
                    $error_message = $response['error']['message'];
                }

                wp_send_json(
                    array(
                        'message' => $error_message,
                        'status'  => 'error'
                    )
                );
            }

            return json_decode( $request['body'], true );
        }
    }