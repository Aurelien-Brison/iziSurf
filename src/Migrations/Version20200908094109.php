<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200908094109 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE search (id INT AUTO_INCREMENT NOT NULL, spot_id INT NOT NULL, user_id INT NOT NULL, ride_id INT DEFAULT NULL, city_departure VARCHAR(150) NOT NULL, departure_date DATE NOT NULL, return_date DATE NOT NULL, available_seat INT NOT NULL, board_max INT NOT NULL, board_size_max NUMERIC(3, 1) NOT NULL, is_same_gender INT NOT NULL, city_latitude VARCHAR(10) NOT NULL, city_longitude VARCHAR(10) NOT NULL, is_notified_when_result TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_B4F0DBA72DF1D37C (spot_id), INDEX IDX_B4F0DBA7A76ED395 (user_id), INDEX IDX_B4F0DBA7302A8A70 (ride_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE search ADD CONSTRAINT FK_B4F0DBA72DF1D37C FOREIGN KEY (spot_id) REFERENCES spot (id)');
        $this->addSql('ALTER TABLE search ADD CONSTRAINT FK_B4F0DBA7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE search ADD CONSTRAINT FK_B4F0DBA7302A8A70 FOREIGN KEY (ride_id) REFERENCES ride (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE search');
    }
}
