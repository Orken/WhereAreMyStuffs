<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191101230659 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE item ADD tree_root INT DEFAULT NULL, ADD parent_id INT DEFAULT NULL, ADD lft INT NOT NULL, ADD lvl INT NOT NULL, ADD rgt INT NOT NULL');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251EA977936C FOREIGN KEY (tree_root) REFERENCES item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E727ACA70 FOREIGN KEY (parent_id) REFERENCES item (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_1F1B251EA977936C ON item (tree_root)');
        $this->addSql('CREATE INDEX IDX_1F1B251E727ACA70 ON item (parent_id)');
        $this->addSql('CREATE INDEX lft_ix ON item (lft)');
        $this->addSql('CREATE INDEX rgt_ix ON item (rgt)');
        $this->addSql('CREATE INDEX lvl_ix ON item (lvl)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251EA977936C');
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251E727ACA70');
        $this->addSql('DROP INDEX IDX_1F1B251EA977936C ON item');
        $this->addSql('DROP INDEX IDX_1F1B251E727ACA70 ON item');
        $this->addSql('DROP INDEX lft_ix ON item');
        $this->addSql('DROP INDEX rgt_ix ON item');
        $this->addSql('DROP INDEX lvl_ix ON item');
        $this->addSql('ALTER TABLE item DROP tree_root, DROP parent_id, DROP lft, DROP lvl, DROP rgt');
    }
}
