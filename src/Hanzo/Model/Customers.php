<?php /* vim: set sw=4: */

namespace Hanzo\Model;

use PropelPDO;

use Hanzo\Model\om\BaseCustomers;
use Hanzo\Model\CustomersQuery;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * Skeleton subclass for representing a row from the 'customers' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.home/un/Documents/Arbejde/Pompdelux/www/hanzo/Symfony/src/Hanzo/Model
 */
class Customers extends BaseCustomers implements AdvancedUserInterface
{
    protected $acl;


    /**
     * shortcut for access checks on the customer.
     *
     * @return boolean
     */
    public function isGranted($role)
    {
        if (empty($this->acl)) {
            $this->acl = Hanzo::getInstance()->container->get('security.context');
        }

        return $this->acl->isGranted($role);
    }

    /**
     * login check
     *
     * @return boolean
     */
    public function isLoggedIn()
    {
        return $this->isGranted('IS_AUTHENTICATED_FULLY');
    }


    /**
     * get full name
     */
     public function getName()
     {
         return trim($this->getFirstName() . ' ' . $this->getLastName());
     }


    /**
     * The following methods is needed by the form component.....
     */

    public function getAddresses($criteria = null, PropelPDO $con = null)
    {
        return $this->getAddressess($criteria, $con);
    }

    protected $accept = false;
    public function getAccept()
    {
        return (bool) $this->getName();
    }

    /**
     * validate uniq emails
     *
     * @return boolean
     */
    public function isEmailUniq()
    {
        if ($email = $this->getEmail()) {
            $customer = CustomersQuery::create()->findOneByEmail($email);
            if ($customer instanceof Customers) {
                return false;
            }
        }

        return true;
    }


    private $map = array(
        'consultant' => array(
            'ROLE_CONSULTANT',
            'ROLE_USER',
        ),
        'customer' => array(
            'ROLE_CUSTOMER',
            'ROLE_USER',
        ),
        'employee' => array(
            'ROLE_EMPLOYEE',
            'ROLE_USER',
        ),
        'customers_service' => array(
            'ROLE_USER',
            'ROLE_CUSTOMERS_SERVICE'
        ),
        'admin' => array(
            'ROLE_ADMIN',
            'ROLE_EMPLOYEE',
            'ROLE_SALES',
            'ROLE_USER',
        ),
    );

    // NICETO: should not be hardcoded
    private $extended = [
        // admin
        'hd@pompdelux.dk'        => ['ROLE_ADMIN', 'ROLE_SALES', 'ROLE_EMPLOYEE', 'ROLE_CONSULTANT'],
        'lv@pompdelux.dk'        => ['ROLE_ADMIN', 'ROLE_SALES', 'ROLE_EMPLOYEE', 'ROLE_CONSULTANT'],
        // admin (bellcom)
        'hf@bellcom.dk'          => ['ROLE_ADMIN', 'ROLE_SALES', 'ROLE_EMPLOYEE', 'ROLE_CONSULTANT'],
        'ulrik@bellcom.dk'       => ['ROLE_ADMIN', 'ROLE_SALES', 'ROLE_EMPLOYEE', 'ROLE_CONSULTANT'],
        'mmh@bellcom.dk'         => ['ROLE_ADMIN', 'ROLE_SALES', 'ROLE_EMPLOYEE', 'ROLE_CONSULTANT'],
        'andersbryrup@gmail.com' => ['ROLE_ADMIN', 'ROLE_SALES', 'ROLE_EMPLOYEE', 'ROLE_CONSULTANT'],
        'hanzo@bellcom.dk'       => ['ROLE_ADMIN', 'ROLE_SALES', 'ROLE_EMPLOYEE', 'ROLE_CONSULTANT'],
        // stats
        'pd@pompdelux.dk'        => ['ROLE_STATS', 'ROLE_CONSULTANT', 'ROLE_EMPLOYEE'],
        'mh@pompdelux.dk'        => ['ROLE_STATS', 'ROLE_CONSULTANT', 'ROLE_EMPLOYEE'],
        // sales
        'kk@pompdelux.dk'        => ['ROLE_SALES', 'ROLE_CONSULTANT', 'ROLE_EMPLOYEE'],
        'ak@pompdelux.dk'        => ['ROLE_SALES', 'ROLE_CONSULTANT', 'ROLE_EMPLOYEE'],
        'sj@pompdelux.dk'        => ['ROLE_SALES', 'ROLE_CONSULTANT', 'ROLE_EMPLOYEE'],
        'nj@pompdelux.dk'        => ['ROLE_SALES', 'ROLE_CONSULTANT', 'ROLE_EMPLOYEE'],
        'pc@pompdelux.dk'        => ['ROLE_SALES', 'ROLE_CONSULTANT', 'ROLE_EMPLOYEE'],
        'mc@pompdelux.dk'        => ['ROLE_SALES', 'ROLE_CONSULTANT', 'ROLE_EMPLOYEE'],
        'mle@pompdelux.dk'       => ['ROLE_SALES', 'ROLE_CONSULTANT', 'ROLE_EMPLOYEE'],
        // customer service
        'cs@pompdelux.dk'        => ['ROLE_CUSTOMERS_SERVICE', 'ROLE_EMPLOYEE'],
    ];

    /**
     * {@inheritDoc}
     */
    public function getRoles()
    {
        $group = $this->getGroups();
        $roles = $this->map[$group->getName()];

        if (isset($this->extended[$this->getUsername()])) {
            $roles = array_merge($roles, $this->extended[$this->getUsername()]);
        }

        return $roles;
    }

    /**
     * {@inheritDoc}
     */
    public function getSalt()
    {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function getUsername()
    {
        return $this->getEmail();
    }

    /**
     * {@inheritDoc}
     */
    public function getUser()
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function eraseCredentials() {}

    /**
     * {@inheritDoc}
     */
    public function isEnabled()
    {
        return $this->getIsActive();
    }

    /**
     * {@inheritDoc}
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

} // Customers
