<?php
namespace Otdr\MageApiSubiektGt\Model;

class MailTransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    public function createPdfAttachment($fileContent, $filename)
    {
        if ($fileContent) {


            $attachmentPart = new \Zend\Mime\Part();
            $attachmentPart->setContent($fileContent)
            ->setType(\Zend_Mime::TYPE_OCTETSTREAM)
            ->setFileName($filename)
            ->setEncoding(\Zend_Mime::ENCODING_BASE64) /*Add this*/
            ->setDisposition(\Zend_Mime::DISPOSITION_ATTACHMENT);
            
            return $attachmentPart;
        }
    }

}
?>