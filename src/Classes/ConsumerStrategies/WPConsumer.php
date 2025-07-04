<?php
require_once(dirname(__FILE__) . "/AbstractConsumer.php");

/**
 * Consumes messages and sends them to a host/endpoint using WordPress's HTTP API
 */
class WPMedia_ConsumerStrategies_WPConsumer extends WPMedia_ConsumerStrategies_AbstractConsumer {

    /**
     * @var string the host to connect to (e.g. api.mixpanel.com)
     */
    protected $_host;


    /**
     * @var string the host-relative endpoint to write to (e.g. /engage)
     */
    protected $_endpoint;


    /**
     * @var int timeout The maximum number of seconds to allow cURL call to execute. Default is 30 seconds.
     */
    protected $_timeout;


    /**
     * @var string the protocol to use for the cURL connection
     */
    protected $_protocol;


    /**
     * Creates a new WPConsumer and assigns properties from the $options array
     * @param array $options
     */
    function __construct($options) {
        parent::__construct($options);

        $this->_host = $options['host'];
        $this->_endpoint = $options['endpoint'];
        $this->_timeout = isset($options['timeout']) ? $options['timeout'] : 30;
        $this->_protocol = isset($options['use_ssl']) && $options['use_ssl'] == true ? "https" : "http";
    }


    /**
     * Send post request to the given host/endpoint using WordPress's HTTP API
     *
     * @param array $batch
     * @return bool
     */
    public function persist($batch) {
        if ( count($batch) <= 0 ) {
            return true;
        }

        $url  = $this->_protocol . "://" . $this->_host . $this->_endpoint;
        $data = "data=" . $this->_encode($batch);

        $response = wp_remote_post(
            $url,
            [
                'timeout' => $this->_timeout,
                'body'    => $data,
            ]
        );

        if ( is_wp_error( $response ) ) {
            $this->_handleError( $response->get_error_code(), $response->get_error_message() );
            return false;
        }

        return true;
    }
}
