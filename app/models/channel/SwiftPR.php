<?php
/*
 * Name: Product Returns Channels
 * Description:
 */

namespace Channel;

class SwiftPR extends Channel
{

    public function __construct(\SwiftPR $pr,$name="")
    {
        $this->name = $name;
        $this->resource = $pr;
    }

    public function triggerByName(array $data)
    {
        switch($this->name)
        {
            case 'pr_approval':
                return $this->triggerNewApproval($data);
                break;
            case 'pr_customercare':
                return $this->triggerNewCustomerCare($data);
                break;
            case 'pr_store_pickup':
                return $this->triggerNewStorePickup($data);
                break;
            case 'pr_store_reception':
                return $this->triggerNewStoreReception($data);
                break;
            case 'pr_store_validation':
                return $this->triggerNewStoreValidation($data);
                break;
            case 'pr_credit_note':
                return $this->triggerNewCreditNote($data);
                break;
            default:
                throw new \RuntimeException("Channel name has not been defined");
        }
    }

    /*
     * When new approvals for a retail manager occurs
     */
    private function triggerNewApproval($data)
    {
        $userList = $this->getNodeResponsibleUsers($data['nodeActivity']);
        if(count($userList))
        {
            $this->triggerPresence('pr_approval',['userList'=>$userList,'html'=>\View::make('')]);
        }
    }

    private function triggerNewCustomerCare($data)
    {
        $userList = $this->getNodeResponsibleUsers($data['nodeActivity']);
        if(count($userList))
        {

        }
    }

    private function triggerNewStorePickup($data)
    {
        $userList = $this->getNodeResponsibleUsers($data['nodeActivity']);
        if(count($userList))
        {

        }
    }

    private function triggerNewStoreReception($data)
    {
        $userList = $this->getNodeResponsibleUsers($data['nodeActivity']);
        if(count($userList))
        {

        }
    }

    private function triggerNewStoreValidation($data)
    {
        $userList = $this->getNodeResponsibleUsers($data['nodeActivity']);
        if(count($userList))
        {

        }
    }

    private function triggerNewCreditNote($data)
    {
        $userList = $this->getNodeResponsibleUsers($data['nodeActivity']);
        if(count($userList))
        {

        }
    }

}
