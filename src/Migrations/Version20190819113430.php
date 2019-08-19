<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190819113430 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE deck DROP FOREIGN KEY FK_4FAC363755E35EC9');
        $this->addSql('DROP INDEX IDX_4FAC363755E35EC9 ON deck');
        $this->addSql('ALTER TABLE deck DROP last_expansion_id');
        $this->addSql('ALTER TABLE decklist DROP FOREIGN KEY FK_ED030EC655E35EC9');
        $this->addSql('DROP INDEX IDX_ED030EC655E35EC9 ON decklist');
        $this->addSql('ALTER TABLE decklist DROP last_expansion_id');
        $this->addSql('ALTER TABLE user DROP donation');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE deck ADD last_expansion_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE deck ADD CONSTRAINT FK_4FAC363755E35EC9 FOREIGN KEY (last_expansion_id) REFERENCES expansion (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_4FAC363755E35EC9 ON deck (last_expansion_id)');
        $this->addSql('ALTER TABLE decklist ADD last_expansion_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE decklist ADD CONSTRAINT FK_ED030EC655E35EC9 FOREIGN KEY (last_expansion_id) REFERENCES expansion (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_ED030EC655E35EC9 ON decklist (last_expansion_id)');
        $this->addSql('ALTER TABLE user ADD donation INT NOT NULL');
    }
}
