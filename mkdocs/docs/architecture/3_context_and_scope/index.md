# Context and scope

## Business Context

**<Diagram or Table\>**

![test](img/test.drawio.png)

**<optionally: Explanation of external domain interfaces\>**

## Technical Context

**<Diagram or Table\>**

``` mermaid
graph LR
  A[Start] --> B{Error?};
  B -->|Yes| C[Hmm...];
  C --> D[Debug];
  D --> B;
  B ---->|No| E[Yay!];
```

**<optionally: Explanation of technical interfaces\>**

**<Mapping Input/Output to Channels\>**