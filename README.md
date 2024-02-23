# Competition Invite Refactor 

This repository showcases a segment of a larger project, specifically focusing on refactoring the competition invite functionality. Due to constraints and the proprietary nature of the full project, it's not possible to share everything. Here, I've shared the section of the code I was responsible for refactoring, with other parts omitted for simplicity.

## Overview

The primary aim of this refactor was to enhance the code's structure and design. This was achieved by implementing the Command and Strategy design patterns, along with adhering to SOLID principles, to improve flexibility and testability. An additional key aspect of this refactor was the use of the  trait, which facilitated sharing common invitation methods across different parts of the application.

## Project Structure

The project is divided into two main sections:

- `before-refactor/`: Contains the original code before any refactoring.
- `after-refactor/`: Contains the code after the refactoring process, highlighting the use of design patterns and  trait.

## Objectives

- **Command Design Pattern**: Used to encapsulate each request as an object, making operations and client requests more manageable and extendable.
- **Strategy Design Pattern**: Enabled dynamic selection of the invitation method (for both registered and unregistered users), making the system more adaptable.
- **SOLID Principles**: The refactoring process aimed to closely follow SOLID principles to ensure that the code is maintainable and scalable.
- **Trait**: Shared common invitation logic across different strategies, promoting code reuse and reducing duplication.

## Usage

To see the impact of the refactoring, you can compare the files in each folder. This comparison will clearly illustrate the improvements and the applied design patterns.

## Contribution

Suggestions for further improvements are welcome. Feel free to contribute by creating an Issue or a Pull Request.

## License

This project is released under the MIT License. For more details, see the `LICENSE` file.
