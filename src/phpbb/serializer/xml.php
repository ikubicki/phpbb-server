<?php

namespace phpbb\serializer;

use phpbb\response;
use XMLWriter;

/**
 * Get rid of unwanted characters form XML tag names
 * 
 * @author ikubicki
 * @param mixed $key
 * @return string
 */
function sanitizeKey($key): string
{
    if (is_numeric($key)) {
        return "number:{$key}";
    }
    if ($key == '*') {
        return 'id:any';
    }
    if (strpos($key, '-')) {
        return 'id:' . $key;
    }
    return $key;
}

/**
 * XML serializer
 */
class xml extends abstraction
{

    /**
     * @var response $response
     */
    protected response $response;

    /**
     * XML serializer
     * 
     * @author ikubicki
     * @return string
     */
    public function __toString(): string
    {
        $xml = $this->createDocument();
        $this->load($xml, 'response', $this->response->body ?? null);
        return $xml->outputMemory(true);
    }

    /**
     * Creates an XML document
     * 
     * @author ikubicki
     * @return XMLWriter
     */
    private function createDocument(): XMLWriter
    {
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->startDocument('1.0');
        return $xml;
    }

    /**
     * Loads data into XML document
     * 
     * @author ikubicki
     * @param XMLWriter $xml
     * @param string $key
     * @param mixed $data
     * @return XMLWriter
     */
    private function load(XMLWriter $xml, string $key, mixed $data): XMLWriter
    {
        if (!$key) {
            return $xml;
        }
        $key = sanitizeKey($key);
        if (is_object($data)) {
            $data = json_decode(json_encode($data), true);
        }
        if (is_array($data)) {
            $xml->startElement($key);
            foreach($data as $property => $value) {
                $this->load($xml, $property, $value);
            }
            $xml->endElement();
        }
        else {
            $xml->writeElement($key, $data);
        }
        return $xml;
    }
}