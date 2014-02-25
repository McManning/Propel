<?php

require_once dirname(__FILE__) . '/../../tools/helpers/bookstore/BookstoreTestBase.php';

/**
 * Tests for replicating one-to-one relations while performing a deep copy
 *
 * @author     Chase McManning
 * @version    $Revision$
 * @package    misc
 */
class DeepCopyReplicationTest extends BookstoreTestBase
{
    protected function setUp()
    {
        parent::setUp();
        
        BookstoreEmployeePeer::doDeleteAll();
        BookstoreEmployeeAccountPeer::doDeleteAll();
    }

    /**
     * Simple variation
     */
    public function testDeepCopyOneToOneSimple()
    {
        $a = new BookstoreEmployee();
        $a->setName('Joe');
        $a->setJobTitle('Event Organizer');
        
        $aAcc = new BookstoreEmployeeAccount();
        $aAcc->setBookstoreEmployee($a);
        $aAcc->setPassword('mysecret');
        
        $a->save();
        
        $b = $a->copy(true);
        $b->setName('Joe Copy');
        
        $b->save();
        
        $bAcc = BookstoreEmployeeAccountQuery::create()->findOneByBookstoreEmployee($b);

        $count = BookstoreEmployeeQuery::create()->count();
        $countAcc = BookstoreEmployeeAccountQuery::create()->count();
        
        $this->assertEquals(2, $count, 'Single replication of BookstoreEmployee account row must be created');
        $this->assertEquals(2, $countAcc, 'Single replication of BookstoreEmployeeAccount row must be created');
        $this->assertTrue($bAcc instanceof BookstoreEmployeeAccount, 'Cloned account returns a one-to-one relationship with employee');
        $this->assertEquals($b->getBookstoreEmployeeAccount(), $bAcc, 'Cloned employee references cloned account');
    }
    
    /**
     * Variant without instance pooling
     */
    public function testDeepCopyOneToOneNoInstancePooling()
    {
        $a = new BookstoreEmployee();
        $a->setName('Joe');
        $a->setJobTitle('Event Organizer');
        $a->save();

        $aAcc = new BookstoreEmployeeAccount();
        $aAcc->setBookstoreEmployee($a);
        $aAcc->setPassword('mysecret');
        $aAcc->save();

        // $b being a deep copy of $a
        $b = $a->copy(true);
        $b->setName('Joe Copy');
        
        $b->save();

        // Pull fresh from the DB
        \Propel::disableInstancePooling();
        $bAcc = BookstoreEmployeeAccountQuery::create()->findOneByBookstoreEmployee($b);

        $count = BookstoreEmployeeQuery::create()->count();
        $countAcc = BookstoreEmployeeAccountQuery::create()->count();
        
        \Propel::enableInstancePooling();

        $this->assertEquals(2, $count, 'Single replication of BookstoreEmployee account row must be created');
        $this->assertEquals(2, $countAcc, 'Single replication of BookstoreEmployeeAccount row must be created');
        $this->assertTrue($bAcc instanceof BookstoreEmployeeAccount, 'Cloned account returns a one-to-one relationship with employee');
        $this->assertEquals($b->getBookstoreEmployeeAccount(), $bAcc, 'Cloned employee references cloned account');
    }
    
}

