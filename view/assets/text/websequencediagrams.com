title C6 MVC Structure

Browser->+Index: 1
Index->+C6: 2
C6->C6: 3
C6->-Index: 4
Index->+C6: 5
C6->+Bootstrap: 6
Bootstrap->Bootstrap: 7
Bootstrap->+Controller: 8
Controller-->-Bootstrap: 9
opt
opt
Bootstrap->+Model: 10
Model-->-Bootstrap: 11
end
opt
Bootstrap->+View: 12
note over View,Browser: 13
View-->-Bootstrap: 14
end
end
Bootstrap-->-C6: 15
C6-->-Index: 16
Index->-Browser: 17




