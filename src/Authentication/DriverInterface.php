<?php
namespace FMUP\Authentication;

/**
 * Defines methods to implement for each driver
 * @author sweffling
 */
interface DriverInterface
{
    /**
     * Set a user in driver
     * @param UserInterface $user
     * @return $this
     */
    public function set(UserInterface $user);
    
    /**
     * Get the user in the driver
     * @return UserInterface|null $user
     */
    public function get();
    
    /**
     * Clear the user from driver
     * @return $this
     */
    public function clear();
}
