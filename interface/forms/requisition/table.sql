--
-- Table structure for table `requisition`
--

CREATE TABLE `requisition` (
  `id` bigint(19) NOT NULL,
  `req_id` varchar(90) NOT NULL,
  `pid` int(5) NOT NULL,
  `lab_id` bigint(35) NOT NULL
) ENGINE=InnoDB;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `requisition`
--
ALTER TABLE `requisition`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `requisition`
--
ALTER TABLE `requisition`
  MODIFY `id` bigint(19) NOT NULL AUTO_INCREMENT;