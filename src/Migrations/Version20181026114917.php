<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181026114917 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE question_reaction (id INT AUTO_INCREMENT NOT NULL, question_id INT NOT NULL, reaction_id INT NOT NULL, INDEX IDX_C7DFB7FB1E27F6BF (question_id), INDEX IDX_C7DFB7FB813C7171 (reaction_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE question_reaction ADD CONSTRAINT FK_C7DFB7FB1E27F6BF FOREIGN KEY (question_id) REFERENCES question_answer (id)');
        $this->addSql('ALTER TABLE question_reaction ADD CONSTRAINT FK_C7DFB7FB813C7171 FOREIGN KEY (reaction_id) REFERENCES reaction_reason (id)');
        $this->addSql('DROP TABLE question_answer_reaction_reason');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE question_answer_reaction_reason (id INT AUTO_INCREMENT NOT NULL, question_answer_id INT NOT NULL, reaction_reason_id INT NOT NULL, INDEX IDX_F823CE21A3E60C9C (question_answer_id), INDEX IDX_F823CE213DEAC6DE (reaction_reason_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE question_answer_reaction_reason ADD CONSTRAINT FK_F823CE213DEAC6DE FOREIGN KEY (reaction_reason_id) REFERENCES reaction_reason (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE question_answer_reaction_reason ADD CONSTRAINT FK_F823CE21A3E60C9C FOREIGN KEY (question_answer_id) REFERENCES question_answer (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE question_reaction');
    }
}
