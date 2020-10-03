<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200930090125 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE review ADD ride_id INT NOT NULL');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6302A8A70 FOREIGN KEY (ride_id) REFERENCES ride (id)');
        $this->addSql('CREATE INDEX IDX_794381C6302A8A70 ON review (ride_id)');
        $this->addSql('ALTER TABLE fit DROP FOREIGN KEY FK_504C0B4B302A8A70');
        $this->addSql('ALTER TABLE fit CHANGE ride_id ride_id INT DEFAULT NULL, CHANGE status_id status_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fit ADD CONSTRAINT FK_504C0B4B302A8A70 FOREIGN KEY (ride_id) REFERENCES ride (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE fit DROP FOREIGN KEY FK_504C0B4B302A8A70');
        $this->addSql('ALTER TABLE fit CHANGE ride_id ride_id INT NOT NULL, CHANGE status_id status_id INT NOT NULL');
        $this->addSql('ALTER TABLE fit ADD CONSTRAINT FK_504C0B4B302A8A70 FOREIGN KEY (ride_id) REFERENCES ride (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6302A8A70');
        $this->addSql('DROP INDEX IDX_794381C6302A8A70 ON review');
        $this->addSql('ALTER TABLE review DROP ride_id');
    }
}
