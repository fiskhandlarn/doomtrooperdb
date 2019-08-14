<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190814065623 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE deck DROP FOREIGN KEY FK_4FAC36374448F8DA');
        $this->addSql('DROP INDEX IDX_4FAC36374448F8DA ON deck');
        $this->addSql('ALTER TABLE deck DROP faction_id');
        $this->addSql('ALTER TABLE decklist DROP FOREIGN KEY FK_ED030EC64448F8DA');
        $this->addSql('DROP INDEX IDX_ED030EC64448F8DA ON decklist');
        $this->addSql('ALTER TABLE decklist DROP faction_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE deck ADD faction_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE deck ADD CONSTRAINT FK_4FAC36374448F8DA FOREIGN KEY (faction_id) REFERENCES faction (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_4FAC36374448F8DA ON deck (faction_id)');
        $this->addSql('ALTER TABLE decklist ADD faction_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE decklist ADD CONSTRAINT FK_ED030EC64448F8DA FOREIGN KEY (faction_id) REFERENCES faction (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_ED030EC64448F8DA ON decklist (faction_id)');
    }
}
