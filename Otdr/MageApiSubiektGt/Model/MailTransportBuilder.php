<?php
namespace Otdr\MageApiSubiektGt\Model;

class MailTransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    public function addPdfAttachment($fileContent, $filename)
    {
        if ($fileContent) {
            $this->message->createAttachment(
                $fileContent,
                'application/pdf',
                \Zend_Mime::DISPOSITION_ATTACHMENT,
                \Zend_Mime::ENCODING_BASE64,
                $filename
            );

            return $this;
        }
    }

    public function addImageAttachment($fileContent, $filename)
    {
        if ($fileContent) {
            $this->message->createAttachment(
                $fileContent,
                \Zend_Mime::TYPE_OCTETSTREAM,
                \Zend_Mime::DISPOSITION_ATTACHMENT,
                \Zend_Mime::ENCODING_BASE64,
                $filename
            );

            return $this;
        }
    }
}
?>