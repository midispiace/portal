<?php
/**
 * Offer repository.
 */
namespace Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Utils\Paginator;

/**
 * Class BookmarkRepository.
 *
 * @package Repository
 */
class OfferRepository
{

    /**
     * Tag repository.
     *
     * @var null|\Repository\TagRepository $tagRepository
     */
    protected $tagRepository = null;

    /**
     * TagRepository constructor.
     *
     * @param \Doctrine\DBAL\Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Number of items per page.
     *
     * const int NUM_ITEMS
     */
    const NUM_ITEMS = 10;

    /**
     * Doctrine DBAL connection.
     *
     * @var \Doctrine\DBAL\Connection $db
     */
    protected $db;


    /**
     * Fetch all records.
     *
     * @return array Result
     */
    public function findAll()
    {
        $queryBuilder = $this->queryAll();

        return $queryBuilder->execute()->fetchAll();
    }

    /**
     * Get records paginated.
     *
     * @param int $page Current page number
     *
     * @return array Result
     */
    public function findAllPaginated($page = 1)
    {
        $countQueryBuilder = $this->queryAll()
            ->select('COUNT(DISTINCT o.numer) AS total_results')
            ->setMaxResults(1);

        $paginator = new Paginator($this->queryAll(), $countQueryBuilder);
        $paginator->setCurrentPage($page);
        $paginator->setMaxPerPage(self::NUM_ITEMS);

        return $paginator->getCurrentPageResults();
    }

    /**
     * Find one record.
     *
     * @param string $id Element id
     *
     * @return array|mixed Result
     */
    public function findOneById($id)
    {
        $queryBuilder = $this->queryAll();
        $queryBuilder->where('o.numer = :id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetch();

        return $result;
    }

    /**
     * Save record.
     *
     * @param array $offer Bookmark
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function save($offer)
    {
        $this->db->beginTransaction();

        try {
            $currentDateTime = new \DateTime();
            $offer['modified_at'] = $currentDateTime->format('Y-m-d H:i:s');
            $tagsIds = isset($offer['tags']) ? array_column($offer['tags'], 'id') : [];
            unset($offer['tags']);

            if (isset($offer['id']) && ctype_digit((string) $offer['id'])) {
                // update record
                $offerId = $offer['id'];
                unset($offer['id']);
                $this->removeLinkedTags($offerId);
                $this->addLinkedTags($offerId, $tagsIds);
                $this->db->update('si_offers', $offer, ['id' => $offerId]);
            } else {
                // add new record
                $offer['created_at'] = $currentDateTime->format('Y-m-d H:i:s');

                $this->db->insert('si_offers', $offer);
                $offerId = $this->db->lastInsertId();
                $this->addLinkedTags($offerId, $tagsIds);
            }
            $this->db->commit();
        } catch (DBALException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Remove record.
     *
     * @param array $offer Bookmark
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return boolean Result
     */
    public function delete($offer)
    {
        $this->db->beginTransaction();

        try {
            $this->removeLinkedTags($offer['id']);
            $this->db->delete('si_offers', ['id' => $offer['id']]);
            $this->db->commit();
        } catch (DBALException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Query all records.
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder Result
     */
    protected function queryAll()
    {
        $queryBuilder = $this->db->createQueryBuilder();

        return $queryBuilder->select(
            'o.numer',
            'o.osoba_id',
            'o.tresc',
            'o.kategoria_id',
            'o.data_wydarzenia',
            'o.data_dodania',
            'o.miasto_id',
            'o.wojewodztwo_id'
        )->from('ogloszenie', 'o');
    }

}