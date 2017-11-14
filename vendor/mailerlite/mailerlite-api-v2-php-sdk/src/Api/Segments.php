<?php

namespace MailerLiteApi\Api;

use MailerLiteApi\Common\ApiAbstract;

class Segments extends ApiAbstract {

    protected $endpoint = 'segments';

    /**
     * Retrieve double opt in status
     *
     * @return mixed
     */
    public function getSegments( $params = [] )
    {
        $endpoint = $this->endpoint;

        $params = array_merge($this->prepareParams(), $params);

        $response = $this->restClient->get( $endpoint, $params );

        return $response['body'];
    }

    public function createSegment( $groupId, $params = [] ) {

        $endpoint = $this->endpoint;

        $params = array_merge($this->prepareParams(), $params );

        $response = $this->restClient->post( $endpoint, $params );

        return $response['body'];
    }




    /*
    public function setDoubleOptin( $status ) {

        $endpoint = $this->endpoint . '/double_optin';

        $params = array_merge($this->prepareParams(), ['enable' => $status] );

        $response = $this->restClient->post( $endpoint, $params );

        return $response['body'];
    }
    */

}