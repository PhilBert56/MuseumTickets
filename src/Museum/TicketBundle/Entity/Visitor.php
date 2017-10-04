<?php


namespace Museum\TicketBundle\Entity;


use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="visitor")
 */

class Visitor
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $name;


    /**
     * @ORM\Column(type="string")
     */
    private $firstName;


    /**
     * @ORM\Column(type= "date")
     */
    private $birthDate;


    /**
     * @ORM\Column(type="string")
     */
    private $country;


	/**
     * @ORM\Column(type="boolean")
     */
    private $reducePrice;



    /**
     * @ORM\OneToOne(targetEntity="Ticket", mappedBy="visitor", cascade={"persist"})
     */
    private $ticket;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }



    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }



    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }



    /**
     * @return mixed
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * @param mixed $birthDate
     */
    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;
    }


    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }


    /**
     * @return mixed
     */
    public function getReducePrice()
    {
        return $this->reducePrice;
    }


    public function setReducePrice($reducePrice)
    {
        $this->reducePrice = $reducePrice;
    }


    /**
     * @return mixed
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @param mixed $ticket
     */
    public function setTicket($ticket)
    {
        $this->ticket = $ticket;
    }


    public function age($dateOfBirth, $dateOfVisit) {

    /* Retourne l'âge qu'aura la personne née le $dateOfBirth (objet date) à la date $date (objet date)*/
        $year_diff  = $dateOfVisit->format("Y") - $dateOfBirth->format("Y");
        $month_diff = $dateOfVisit->format("m") - $dateOfBirth->format("m");
        $day_diff   = $dateOfVisit->format("d") - $dateOfBirth->format("d");
        if ($month_diff < 0) $year_diff--;
        if ($month_diff==0 && $day_diff <= 0  ) $year_diff--;
        return $year_diff;
    }

    public function setTicketInfo($dateOfVisit, $priceFromBirthDateServive) {

        $ticket = $this->getTicket();
        $ticket->setDateOfVisit($dateOfVisit);

        /* Calcul du code tarif */
        //$priceFromBirthDateServive = $controller->container->get('museum.priceFromBirthDate');
        $priceCode = $priceFromBirthDateServive->getPriceCode($this,$dateOfVisit);

        $price = $priceFromBirthDateServive->getPrice($priceCode);
        /* Vérifier si demi-journée, si vrai, prix divisé par 2 */
        if ($ticket->getHalfDay() ) $price = $price / 2;

        $ticket->setPriceCode($priceCode);
        $ticket->setPrice($price);

        return $ticket;
    }



}
