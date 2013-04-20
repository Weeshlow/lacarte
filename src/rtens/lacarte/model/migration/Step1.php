<?php
namespace rtens\lacarte\model\migration;

class Step1 extends BaseStep {

    public function up() {
        $this->db->execute("CREATE TABLE groups (
                    'id' INTEGER NOT NULL,
                    'name' TEXT(255),
                    'adminEmail' TEXT(255),
                    'adminPassword' TEXT(255),
                    PRIMARY KEY ('id'));");

        $this->db->execute("CREATE TABLE users (
                    'id' INTEGER NOT NULL,
                    'groupId' INTEGER NOT NULL,
                    'name' TEXT(255),
                    'email' TEXT(255),
                    'key' TEXT(32),
                    PRIMARY KEY ('id'));");
    }

    public function down() {
        $this->db->execute("DROP TABLE groups;");
        $this->db->execute("DROP TABLE users;");
    }
}
