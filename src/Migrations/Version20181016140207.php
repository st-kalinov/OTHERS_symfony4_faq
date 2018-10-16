<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181016140207 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE question_reaction ADD question_id INT NOT NULL, ADD reaction_id INT NOT NULL');
        $this->addSql('ALTER TABLE question_reaction ADD CONSTRAINT FK_C7DFB7FB1E27F6BF FOREIGN KEY (question_id) REFERENCES question_answer (id)');
        $this->addSql('ALTER TABLE question_reaction ADD CONSTRAINT FK_C7DFB7FB813C7171 FOREIGN KEY (reaction_id) REFERENCES reaction_reason (id)');
        $this->addSql('CREATE INDEX IDX_C7DFB7FB1E27F6BF ON question_reaction (question_id)');
        $this->addSql('CREATE INDEX IDX_C7DFB7FB813C7171 ON question_reaction (reaction_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE question_reaction DROP FOREIGN KEY FK_C7DFB7FB1E27F6BF');
        $this->addSql('ALTER TABLE question_reaction DROP FOREIGN KEY FK_C7DFB7FB813C7171');
        $this->addSql('DROP INDEX IDX_C7DFB7FB1E27F6BF ON question_reaction');
        $this->addSql('DROP INDEX IDX_C7DFB7FB813C7171 ON question_reaction');
        $this->addSql('ALTER TABLE question_reaction DROP question_id, DROP reaction_id');
    }
}
